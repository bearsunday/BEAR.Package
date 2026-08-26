<?php

declare(strict_types=1);

namespace BEAR\Package;

use ArrayObject;
use BEAR\AppMeta\AbstractAppMeta;
use BEAR\AppMeta\Meta;
use BEAR\Package\Compiler\ClassTracker;
use BEAR\Package\Compiler\CompileAutoload;
use BEAR\Package\Compiler\CompileClassMetaInfo;
use BEAR\Package\Compiler\CompileObjectGraph;
use BEAR\Package\Compiler\CompileSteps;
use BEAR\Package\Compiler\DotCommand;
use BEAR\Package\Compiler\FakeRun;
use BEAR\Package\Compiler\FilePutContents;
use BEAR\Package\Exception\PharEntryNotFoundException;
use BEAR\Package\Exception\PreloadRecordException;
use BEAR\Package\Injector\CompileMarker;
use BEAR\Package\Module\ResourceObjectModule;
use BEAR\Resource\NamedParameterInterface;
use BEAR\Sunday\Extension\Application\AppInterface;
use FilesystemIterator;
use Ray\Compiler\Annotation\Compile;
use Ray\Di\AbstractModule;
use Ray\Di\Injector as RayInjector;
use Ray\Di\InjectorInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;

use function assert;
use function dirname;
use function escapeshellarg;
use function file_exists;
use function is_dir;
use function is_file;
use function is_float;
use function memory_get_peak_usage;
use function microtime;
use function mkdir;
use function number_format;
use function passthru;
use function printf;
use function realpath;
use function rmdir;
use function sprintf;
use function str_replace;
use function str_starts_with;
use function unlink;

use const PHP_BINARY;
use const PHP_BINDIR;
use const PHP_EOL;
use const PHP_SAPI;

/**
 * @psalm-import-type AppName from Types
 * @psalm-import-type Context from Types
 * @psalm-import-type AppDir from Types
 * @psalm-import-type BuildDir from Types
 * @psalm-import-type StubEntry from Types
 * @psalm-import-type CompileReport from Types
 */
final class Compiler
{
    /** @var ArrayObject<int, string> */
    private ArrayObject $classes;
    private AbstractAppMeta $appMeta;

    /** @var Context */
    private string $context;
    private InjectorInterface|null $injector = null;

    /**
     * What the compile was asked for: the preload worker builds the same Meta from it.
     *
     * @var array{AppName, Context, AppDir}
     */
    private array $preloadJob;

    /**
     * @param AppName $appName application name "MyVendor|MyProject"
     * @param Context $context application context "prod-app"
     * @param AppDir  $appDir  application path
     */
    public function __construct(string $appName, string $context, string $appDir)
    {
        $meta = new Meta($appName, $context, $appDir);
        $this->preloadJob = [$meta->name, $context, $appDir];
        $this->prepare($context, $appDir, $meta);
        // The module tree, not the constructor Meta, settles where this compile writes.
        $this->appMeta = (new RayInjector($this->module(), $meta->buildDir . '/di'))->getInstance(AbstractAppMeta::class);
    }

    public function __invoke(): int
    {
        // Before clean(): the recorder refuses this context three steps later, with the tree
        // already emptied for a compile that was never going to finish.
        if (! $this->isCompiled()) {
            throw PreloadRecordException::notCompiled($this->context);
        }

        $this->clean();
        $report = $this->compile();
        echo PHP_EOL;
        printf("Compilation took %s seconds and used %sMB of memory\n", $report['time'], $report['memory']);
        printf("Compiled: %d resource classes\n", $report['compiled']);
        foreach ($report['steps'] as $step => $count) {
            printf("Compile step %s: %d artifacts\n", $step, $count);
        }

        printf("Preload compile: %s\n", $report['preload']);
        printf("Object graph diagram: %s\n", $report['dot']);

        return $this->dumpAutoload();
    }

    /**
     * Pack what compile() left on disk into {appDir}/app.phar.
     *
     * @param StubEntry|null $entry relative to appDir (default public/index.php)
     *
     * @throws PharEntryNotFoundException
     */
    public function phar(string|null $entry = null): int
    {
        [, , $appDir] = $this->preloadJob;
        $entry ??= 'public/index.php';
        if (! is_file($appDir . '/' . $entry)) {
            throw new PharEntryNotFoundException($appDir . '/' . $entry);
        }

        $exitCode = 1;
        passthru(self::pharWorkerCommand($appDir, $this->appMeta->buildDir, $entry), $exitCode);

        return $exitCode;
    }

    /**
     * Write preload.php in a process that boots from the compiled scripts.
     *
     * The compile process cannot measure a boot: it holds the compiler and the module tree,
     * and it loaded the boot path itself before the tracker was installed. Only a process
     * that does nothing but boot the application knows what a request loads.
     *
     * @param array{AppName, Context, AppDir} $job
     *
     * @return non-empty-string the generated preload.php
     *
     * @throws PreloadRecordException
     */
    private static function recordPreloadInChildProcess(array $job): string
    {
        [$appName, $context, $appDir] = $job;
        $command = self::workerCommand('preload-worker.php', $appName, $context, $appDir);
        $appDirRealpath = realpath($appDir);
        assert($appDirRealpath !== false);
        $preload = $appDirRealpath . '/preload.php';
        // Remove it first so the check below proves this worker wrote it, not the last deploy.
        @unlink($preload);
        $exitCode = 1;
        passthru($command, $exitCode);
        if ($exitCode !== 0 || ! file_exists($preload)) {
            throw PreloadRecordException::workerFailed($context, $exitCode);
        }

        return $preload;
    }

    /**
     * @param AppName $appName
     * @param Context $context
     * @param AppDir  $appDir
     */
    private static function workerCommand(string $worker, string $appName, string $context, string $appDir): string
    {
        return sprintf(
            '%s %s %s %s %s',
            escapeshellarg(self::phpBinary()),
            escapeshellarg(dirname(__DIR__) . '/bin/' . $worker),
            escapeshellarg($appName),
            escapeshellarg($context),
            escapeshellarg($appDir),
        );
    }

    /**
     * @param AppDir   $appDir
     * @param BuildDir $buildDir the compile's own, so the pack derives no path of the host's
     */
    private static function pharWorkerCommand(string $appDir, string $buildDir, string $entry): string
    {
        return sprintf(
            // The empty argument is the worker's optional output.
            '%s -d phar.readonly=0 %s %s %s %s %s',
            escapeshellarg(self::phpBinary()),
            escapeshellarg(dirname(__DIR__) . '/bin/phar-worker.php'),
            escapeshellarg($appDir),
            escapeshellarg($buildDir),
            escapeshellarg($entry),
            escapeshellarg(''),
        );
    }

    private static function phpBinary(): string
    {
        // PHP_BINARY is the server binary under fpm and empty under some embedded SAPIs:
        // the worker needs the interpreter, not whatever is running this compile.
        return PHP_SAPI === 'cli' ? PHP_BINARY : PHP_BINDIR . '/php';
    }

    /**
     * Empty what the compile owns, then recreate the script directory.
     *
     * The whole build directory, not only the DI scripts: a compile step dropped from the
     * module tree would otherwise keep shipping the artifacts of the run that still had it.
     *
     * The tmp directory only when it sits in the tree, where the deployment artifact would
     * carry its stale caches. One outside is the runtime's: shared by every context whose
     * module tree reaches the declaring install, and possibly live while this compile runs.
     *
     * preload.php, autoload.php and app.phar stay: each is replaced by whatever writes it, at
     * the moment it writes, so a compile that dies partway leaves the last one's files alone.
     */
    public function clean(): void
    {
        $scriptDir = $this->appMeta->buildDir . '/di';
        if ($this->writesInTree()) {
            $this->emptyDirectory($this->appMeta->tmpDir);
        }

        $this->emptyDirectory($this->appMeta->buildDir);
        $this->ensureDirectory($scriptDir);
        $this->injector = null;
    }

    private function writesInTree(): bool
    {
        return str_starts_with(
            str_replace('\\', '/', $this->appMeta->tmpDir),
            str_replace('\\', '/', $this->appMeta->appDir) . '/',
        );
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
        $injector = $this->injector();
        $module = (new Module())($this->appMeta, $this->context);
        $compiler = new \Ray\Compiler\Compiler();
        $scriptDir = $this->appMeta->buildDir . '/di';
        $this->ensureDirectory($scriptDir);
        $compiler->compile($module, $scriptDir);
        $steps = $injector->getInstance(CompileSteps::class)($this->appMeta->buildDir);
        // Marker after the DI scripts and the steps: it claims the whole build is on disk (#483).
        // Only for a context that boots from them - a marker is what lets a boot return the
        // scripts without assembling a module tree, and a per-request context must not.
        if ($this->isCompiled()) {
            CompileMarker::write($scriptDir, $this->appMeta->name, $this->context);
        }

        // Compile class meta info (annotations and named parameters)
        $compiled = $this->compileClassMetaInfo();

        // Preload last: the worker boots from the finished artifact - scripts, marker and
        // meta caches all in place - so it loads what a deployed first request loads.
        $preload = self::recordPreloadInChildProcess($this->preloadJob);

        $dot = (new CompileObjectGraph(self::filePutContents(), $this->appMeta->logDir, new DotCommand()))($module);
        $start = self::getRequestTime($_SERVER['REQUEST_TIME_FLOAT'] ?? null);
        $time = number_format(microtime(true) - $start, 2);
        $memory = number_format(memory_get_peak_usage() / (1024 * 1024), 3);
        $dotRealpath = realpath($dot);
        assert($dotRealpath !== false);

        return [
            'time' => $time,
            'memory' => $memory,
            'compiled' => $compiled,
            'preload' => $preload,
            'dot' => $dotRealpath,
            'steps' => $steps,
        ];
    }

    public function dumpAutoload(): int
    {
        /** @var AppDir $appDir */
        $appDir = $this->appMeta->appDir;
        $fakeRun = new FakeRun($this->injector(), $this->context, $this->appMeta);

        return (new CompileAutoload($fakeRun, self::filePutContents(), $this->classes, $appDir, $this->context))();
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
        $namedParams = $this->injector()->getInstance(NamedParameterInterface::class);
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
     * Install the class tracker and the `.compile.php` stubs before any injector is built:
     * an injector loads the application, and stubs that arrive after it never apply.
     *
     * @param Context $context
     * @param AppDir  $appDir
     */
    private function prepare(
        string $context,
        string $appDir,
        AbstractAppMeta $appMeta,
    ): void {
        $tracker = ClassTracker::fromAppDir($appDir);
        $this->classes = $tracker->classes();
        $this->context = $context;
        $this->appMeta = $appMeta;
        $tracker->register();
        $this->hookNullObjectClass($appDir);
    }

    /**
     * clean() drops this: the injector reads the DI scripts, and a stale one fails with
     * Unbound on a binding whose file was just deleted.
     */
    private function injector(): InjectorInterface
    {
        return $this->injector ??= $this->compileInjector();
    }

    private static function filePutContents(): FilePutContents
    {
        /** @var ArrayObject<int, string> $overwritten */
        $overwritten = new ArrayObject();

        return new FilePutContents($overwritten);
    }

    /**
     * Injector for the compile pipeline: never the boot one.
     *
     * PackageInjector::factory() would take its runtime cold path, logging an on-demand
     * compile and writing the marker mid-build. This compile is not the pass in compile():
     * it populates the scripts FakeRun resolves through, the later pass re-emits them after
     * AOP weaving.
     */
    private function compileInjector(): InjectorInterface
    {
        $scriptDir = $this->appMeta->buildDir . '/di';
        $this->ensureDirectory($scriptDir);
        $module = $this->module();
        if (self::isProd($module)) {
            (new \Ray\Compiler\Compiler())->compile($module, $scriptDir);
        }

        $injector = new RayInjector($module, $scriptDir);
        /** @psalm-suppress InvalidArgument */
        $injector->getInstance(AppInterface::class);

        return $injector;
    }

    /** Whether this context boots from compiled scripts rather than assembling per request. */
    private function isCompiled(): bool
    {
        return self::isProd($this->module());
    }

    private function module(): AbstractModule
    {
        $module = (new Module())($this->appMeta, $this->context);
        $module->install(new ResourceObjectModule($this->appMeta->getResourceListGenerator()));

        return $module;
    }

    private static function isProd(AbstractModule $module): bool
    {
        return (bool) $module->getContainer()->getInstance('', Compile::class);
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
}
