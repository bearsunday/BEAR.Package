<?php

declare(strict_types=1);

namespace BEAR\Package;

use ArrayObject;
use BEAR\AppMeta\Meta;
use BEAR\Package\Compile\NewInstance;
use BEAR\Package\Compiler\CompileAutoload;
use BEAR\Package\Compiler\CompileClassMetaInfo;
use BEAR\Package\Compiler\CompileObjectGraph;
use BEAR\Package\Compiler\CompilePreload;
use BEAR\Package\Compiler\FakeRun;
use BEAR\Package\Compiler\FilePutContents;
use BEAR\Package\Provide\Error\NullPage;
use BEAR\Resource\NamedParameterInterface;
use Composer\Autoload\ClassLoader;
use Doctrine\Common\Annotations\Reader;
use RuntimeException;

use function assert;
use function count;
use function file_exists;
use function is_int;
use function memory_get_peak_usage;
use function microtime;
use function number_format;
use function printf;
use function realpath;
use function spl_autoload_functions;
use function spl_autoload_register;
use function spl_autoload_unregister;
use function strpos;

use const PHP_EOL;

final class Compiler
{
    /** @var ArrayObject<int, string> */
    private ArrayObject $classes;
    private Meta $appMeta;
    private NewInstance $newInstance;
    private CompileAutoload $dumpAutoload;
    private CompilePreload $compilePreload;
    private CompileObjectGraph $compilerObjectGraph;

    /**
     * @param string $appName application name "MyVendor|MyProject"
     * @param string $context application context "prod-app"
     * @param string $appDir  application path
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function __construct(string $appName, private string $context, string $appDir, bool $prepend = true)
    {
        /** @var ArrayObject<int, string> $classes */
        $classes = new ArrayObject();
        $this->classes = $classes;
        $this->registerLoader($appDir, $prepend);
        $this->hookNullObjectClass($appDir);
        $this->appMeta = new Meta($appName, $context, $appDir);
        /** @psalm-suppress MixedAssignment (?) */
        $injector = Injector::getInstance($appName, $context, $appDir);
        $this->newInstance = new NewInstance($injector);
        /** @var ArrayObject<int, string> $overWritten */
        $overWritten = new ArrayObject();
        $filePutContents = new FilePutContents($overWritten);
        $fakeRun = new FakeRun($injector, $context, $this->appMeta);
        $this->dumpAutoload = new CompileAutoload($fakeRun, $filePutContents, $this->appMeta, $overWritten, $this->classes, $appDir, $context);
        $this->compilePreload = new CompilePreload($fakeRun, $this->dumpAutoload, $filePutContents, $classes, $context);
        $this->compilerObjectGraph = new CompileObjectGraph($filePutContents, $this->appMeta->logDir);
    }

    /**
     * Compile application
     *
     * @return 0|1 exit code
     */
    public function compile(): int
    {
        $preload = ($this->compilePreload)($this->appMeta, $this->context);
        $module = (new Module())($this->appMeta, $this->context);
        $compiler = new \Ray\Compiler\Compiler();
        $scriptDir = realpath($this->appMeta->appDir) . '/var/di/' . $this->context;
        $compiler->compile($module, $scriptDir);

        // Compile class meta info (annotations and named parameters)
        $this->compileClassMetaInfo();

        echo PHP_EOL;
        $failed = $this->newInstance->getFailed();
        $dot = $failed ? '' : ($this->compilerObjectGraph)($module);
        $start = $_SERVER['REQUEST_TIME_FLOAT'] ?? 0;
        $time = number_format(microtime(true) - $start, 2);
        $memory = number_format(memory_get_peak_usage() / (1024 * 1024), 3);
        echo PHP_EOL;
        printf("Compilation (1/2) took %f seconds and used %fMB of memory\n", $time, $memory);
        printf("Success: %d Failed: %d\n", $this->newInstance->getCompiled(), count($this->newInstance->getFailed()));
        printf("Preload compile: %s\n", $this->dumpAutoload->getFileInfo($preload));
        printf("Object graph diagram: %s\n", realpath($dot));
        foreach ($this->newInstance->getFailed() as $dependencyIndex => $error) {
            printf("UNBOUND: %s for %s \n", $error, $dependencyIndex);
        }

        return $failed ? 1 : 0;
    }

    public function dumpAutoload(): int
    {
        return ($this->dumpAutoload)();
    }

    private function compileClassMetaInfo(): void
    {
        $injector = Injector::getInstance($this->appMeta->name, $this->context, $this->appMeta->appDir);
        $reader = $injector->getInstance(Reader::class);
        assert($reader instanceof Reader);
        $namedParams = $injector->getInstance(NamedParameterInterface::class);
        assert($namedParams instanceof NamedParameterInterface);

        $compileClassMetaInfo = new CompileClassMetaInfo();
        $resources = $this->appMeta->getResourceListGenerator();
        foreach ($resources as $resource) {
            [$className] = $resource;
            $compileClassMetaInfo($reader, $namedParams, $className);
        }
    }

    /** @SuppressWarnings(PHPMD.BooleanArgumentFlag) */
    private function registerLoader(string $appDir, bool $prepend = true): void
    {
        $this->unregisterComposerLoader();
        $loaderFile = $appDir . '/vendor/autoload.php';
        if (! file_exists($loaderFile)) {
            throw new RuntimeException('no loader');
        }

        $loader = require $loaderFile;
        assert($loader instanceof ClassLoader);
        spl_autoload_register(
            /** @ class-string $class */
            function (string $class) use ($loader): void {
                $loader->loadClass($class);
                if (
                    $class === NullPage::class
                    || is_int(strpos($class, Compiler::class))
                    || is_int(strpos($class, NullPage::class))
                ) {
                    return;
                }

                /** @psalm-suppress NullArgument */
                $this->classes[] = $class;
            },
            true,
            $prepend,
        );
    }

    private function hookNullObjectClass(string $appDir): void
    {
        $compileScript = realpath($appDir) . '/.compile.php';
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
