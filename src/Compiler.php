<?php

declare(strict_types=1);

namespace BEAR\Package;

use ArrayObject;
use BEAR\AppMeta\AbstractAppMeta;
use BEAR\AppMeta\Meta;
use BEAR\Package\Compiler\CompileAutoload;
use BEAR\Package\Compiler\CompileClassMetaInfo;
use BEAR\Package\Compiler\CompileObjectGraph;
use BEAR\Package\Compiler\CompilePreload;
use BEAR\Package\Compiler\FakeRun;
use BEAR\Package\Compiler\FilePutContents;
use BEAR\Package\Injector\PackageInjector;
use BEAR\Package\Provide\Error\NullPage;
use BEAR\Resource\NamedParameterInterface;
use Composer\Autoload\ClassLoader;
use FilesystemIterator;
use Ray\Di\InjectorInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use RuntimeException;
use SplFileInfo;

use function assert;
use function class_exists;
use function dirname;
use function file_exists;
use function interface_exists;
use function is_dir;
use function is_float;
use function is_int;
use function is_string;
use function memory_get_peak_usage;
use function microtime;
use function mkdir;
use function number_format;
use function printf;
use function realpath;
use function rmdir;
use function spl_autoload_functions;
use function spl_autoload_register;
use function spl_autoload_unregister;
use function str_starts_with;
use function strpos;
use function trait_exists;
use function unlink;

use const DIRECTORY_SEPARATOR;
use const PHP_EOL;

/**
 * @psalm-import-type AppName from Types
 * @psalm-import-type Context from Types
 * @psalm-import-type AppDir from Types
 * @psalm-import-type ClassList from Types
 * @psalm-import-type OverwrittenFiles from Types
 * @psalm-import-type CompileReport from Types
 */
final class Compiler
{
    /** @var ArrayObject<int, string> */
    private ArrayObject $classes;
    private AbstractAppMeta $appMeta;

    /** @var Context */
    private string $context;
    private InjectorInterface $injector;
    private CompileAutoload $dumpAutoload;
    private CompilePreload $compilePreload;
    private CompileObjectGraph $compilerObjectGraph;
    private ClassLoader $loader;
    private string $composerDir;

    /** @var array<string, true> */
    private array $composerLoadedFiles = [];

    /**
     * @param AppName $appName application name "MyVendor|MyProject"
     * @param Context $context application context "prod-app"
     * @param AppDir  $appDir  application path
     *
     * @SuppressWarnings("PHPMD.BooleanArgumentFlag")
     */
    public function __construct(string $appName, string $context, string $appDir, bool $prepend = true)
    {
        $meta = new Meta($appName, $context, $appDir);
        // registerLoader / hookNullObjectClass must run before Injector::getInstance
        // (argument evaluation would load the app too early and break .compile.php stubs).
        $this->prepare($context, $appDir, $prepend, $meta, true);
        $this->wire(Injector::getInstance($appName, $context, $appDir));
    }

    /**
     * Create a compiler from an application injector.
     *
     * Meta (including tmpDir / logDir) is taken from the injector so compile
     * uses the same path policy as runtime. Constructor BC is unchanged.
     * Run compilation in a dedicated process because all declared classes become preload candidates.
     *
     * @param Context $context
     *
     * @SuppressWarnings("PHPMD.BooleanArgumentFlag")
     */
    public static function fromInjector(InjectorInterface $injector, string $context, bool $prepend = true): self
    {
        $meta = $injector->getInstance(AbstractAppMeta::class);
        /** @var AppDir $appDir */
        $appDir = $meta->appDir;

        $compiler = (new ReflectionClass(self::class))->newInstanceWithoutConstructor();
        // Skip .compile.php: the injector is already built and app classes are loaded.
        $compiler->prepare($context, $appDir, $prepend, $meta, false);
        $compiler->wire($injector);

        return $compiler;
    }

    /**
     * Full compile pipeline: clean tmpDir, compile DI/preload, dump autoload.
     */
    public function __invoke(): int
    {
        $this->clean();
        // CompiledInjector scripts live under tmpDir/di; rebuild after wipe (bear.compile uses a new process).
        $this->wire(PackageInjector::factory($this->appMeta, $this->context));
        $report = $this->compile();
        echo PHP_EOL;
        printf("Compilation took %s seconds and used %sMB of memory\n", $report['time'], $report['memory']);
        printf("Compiled: %d resource classes\n", $report['compiled']);
        printf("Preload compile: %s\n", $report['preload']);
        printf("Object graph diagram: %s\n", $report['dot']);

        return $this->dumpAutoload();
    }

    /**
     * Remove compiled artifacts under Meta tmpDir (same as bear.compile clean step),
     * then recreate directories needed for a subsequent in-process compile.
     */
    public function clean(): void
    {
        $this->emptyDirectory($this->appMeta->tmpDir);
        // Same path as PackageInjector runtime scriptDir (no override): {tmpDir}/di
        $this->ensureDirectory($this->appMeta->tmpDir . '/di');
    }

    private function emptyDirectory(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
        );
        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            $pathname = $file->getPathname();
            if ($file->isDir()) {
                rmdir($pathname);
                continue;
            }

            unlink($pathname);
        }
    }

    private function ensureDirectory(string $dir): void
    {
        if (is_dir($dir)) {
            return;
        }

        if (! @mkdir($dir, 0777, true) && ! is_dir($dir)) {
            throw new RuntimeException('Unable to create directory: ' . $dir);
        }
    }

    /**
     * Compile application
     *
     * @return CompileReport
     */
    public function compile(): array
    {
        $preload = ($this->compilePreload)($this->appMeta, $this->context);
        $module = (new Module())($this->appMeta, $this->context);
        $compiler = new \Ray\Compiler\Compiler();
        // Same path as PackageInjector runtime scriptDir (no override): {tmpDir}/di
        $scriptDir = $this->appMeta->tmpDir . '/di';
        ! is_dir($scriptDir) && ! @mkdir($scriptDir, 0777, true) && ! is_dir($scriptDir);
        $compiler->compile($module, $scriptDir);

        // Compile class meta info (annotations and named parameters)
        $compiled = $this->compileClassMetaInfo();

        $dot = ($this->compilerObjectGraph)($module);
        $start = self::getRequestTime($_SERVER['REQUEST_TIME_FLOAT'] ?? null);
        $time = number_format(microtime(true) - $start, 2);
        $memory = number_format(memory_get_peak_usage() / (1024 * 1024), 3);
        $dotRealpath = realpath($dot);
        assert($dotRealpath !== false);

        return [
            'time' => $time,
            'memory' => $memory,
            'compiled' => $compiled,
            'preload' => $this->dumpAutoload->getFileInfo($preload),
            'dot' => $dotRealpath,
        ];
    }

    public function dumpAutoload(): int
    {
        return ($this->dumpAutoload)();
    }

    private static function getRequestTime(mixed $requestTime): float
    {
        if (is_float($requestTime)) {
            return $requestTime;
        }

        return 0.0;
    }

    private function compileClassMetaInfo(): int
    {
        $namedParams = $this->injector->getInstance(NamedParameterInterface::class);
        assert($namedParams instanceof NamedParameterInterface);

        $compileClassMetaInfo = new CompileClassMetaInfo();
        $resources = $this->appMeta->getResourceListGenerator();
        $count = 0;
        foreach ($resources as $resource) {
            [$className] = $resource;
            $compileClassMetaInfo($namedParams, $className);
            $count++;
        }

        return $count;
    }

    /**
     * @param Context $context
     * @param AppDir  $appDir
     *
     * @SuppressWarnings("PHPMD.BooleanArgumentFlag")
     */
    private function prepare(
        string $context,
        string $appDir,
        bool $prepend,
        AbstractAppMeta $appMeta,
        bool $loadCompileScript,
    ): void {
        /** @var ArrayObject<int, string> $classes */
        $classes = new ArrayObject();
        $this->classes = $classes;
        $this->context = $context;
        $this->appMeta = $appMeta;
        $this->registerLoader($appDir, $prepend);
        if (! $loadCompileScript) {
            return;
        }

        $this->hookNullObjectClass($appDir);
    }

    private function wire(InjectorInterface $injector): void
    {
        $this->injector = $injector;
        /** @var AppDir $appDir */
        $appDir = $this->appMeta->appDir;
        /** @var ArrayObject<int, string> $overWritten */
        $overWritten = new ArrayObject();
        $filePutContents = new FilePutContents($overWritten);
        $fakeRun = new FakeRun($injector, $this->context, $this->appMeta);
        $this->dumpAutoload = new CompileAutoload($fakeRun, $filePutContents, $this->classes, $appDir, $this->context);
        $this->compilePreload = new CompilePreload(
            $fakeRun,
            $this->dumpAutoload,
            $filePutContents,
            $this->classes,
            $injector,
            $this->isPreloadClass(...),
        );
        $this->compilerObjectGraph = new CompileObjectGraph($filePutContents, $this->appMeta->logDir);
    }

    /** @SuppressWarnings("PHPMD.BooleanArgumentFlag") */
    private function registerLoader(string $appDir, bool $prepend = true): void
    {
        $this->unregisterComposerLoader();
        $loaderFile = $appDir . '/vendor/autoload.php';
        if (! file_exists($loaderFile)) {
            throw new RuntimeException('no loader');
        }

        $loader = require $loaderFile;
        assert($loader instanceof ClassLoader);
        $this->loader = $loader;
        $this->prepareComposerFiles();
        spl_autoload_register(
            /** @ class-string $class */
            function (string $class) use ($loader): void {
                $loader->loadClass($class);
                if ($this->isExcludedClass($class)) {
                    return;
                }

                /** @psalm-suppress NullArgument */
                $this->classes[] = $class;
            },
            true,
            $prepend,
        );
    }

    private function prepareComposerFiles(): void
    {
        $classLoaderFile = (new ReflectionClass(ClassLoader::class))->getFileName();
        assert(is_string($classLoaderFile));
        $composerDir = $this->normalizePath(dirname($classLoaderFile));
        assert(is_string($composerDir));
        $this->composerDir = $composerDir;

        $autoloadFilesPath = $composerDir . '/autoload_files.php';
        if (! file_exists($autoloadFilesPath)) {
            return;
        }

        /** @var array<string, string> $autoloadFiles */
        $autoloadFiles = require $autoloadFilesPath;
        foreach ($autoloadFiles as $autoloadFile) {
            $path = $this->normalizePath($autoloadFile);
            if (! is_string($path)) {
                continue;
            }

            $this->composerLoadedFiles[$path] = true;
        }
    }

    private function isPreloadClass(string $class): bool
    {
        if ($this->isExcludedClass($class)) {
            return false;
        }

        if (! class_exists($class, false) && ! interface_exists($class, false) && ! trait_exists($class, false)) {
            return false;
        }

        /** @var class-string $class */
        $reflection = new ReflectionClass($class);
        if ($reflection->isAnonymous()) {
            return false;
        }

        $fileName = $reflection->getFileName();
        $loaderFile = $this->loader->findFile($class);
        if (! is_string($fileName) || ! is_string($loaderFile)) {
            return false;
        }

        $filePath = $this->normalizePath($fileName);
        $loaderPath = $this->normalizePath($loaderFile);
        if (! is_string($filePath) || $filePath !== $loaderPath) {
            return false;
        }

        return ! $this->isComposerLoadedFile($filePath);
    }

    private function isExcludedClass(string $class): bool
    {
        return $class === NullPage::class
            || is_int(strpos($class, self::class))
            || is_int(strpos($class, NullPage::class));
    }

    private function isComposerLoadedFile(string $filePath): bool
    {
        return str_starts_with($filePath, $this->composerDir . DIRECTORY_SEPARATOR)
            || isset($this->composerLoadedFiles[$filePath]);
    }

    private function normalizePath(string $path): string|false
    {
        if (str_starts_with($path, 'phar://')) {
            return $path;
        }

        return realpath($path);
    }

    private function hookNullObjectClass(string $appDir): void
    {
        $appDirRealpath = realpath($appDir);
        assert($appDirRealpath !== false);
        $compileScript = $appDirRealpath . '/.compile.php';
        if (! file_exists($compileScript)) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }

        require $compileScript;
    }

    private function unregisterComposerLoader(): void
    {
        $autoload = spl_autoload_functions();
        if (! isset($autoload[0])) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }

        spl_autoload_unregister($autoload[0]);
    }
}
