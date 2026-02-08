<?php

declare(strict_types=1);

namespace BEAR\Package;

use ArrayObject;
use BEAR\AppMeta\Meta;
use BEAR\Package\Compiler\CompileAutoload;
use BEAR\Package\Compiler\CompileClassMetaInfo;
use BEAR\Package\Compiler\CompileObjectGraph;
use BEAR\Package\Compiler\CompilePreload;
use BEAR\Package\Compiler\FakeRun;
use BEAR\Package\Compiler\FilePutContents;
use BEAR\Package\Provide\Error\NullPage;
use BEAR\Resource\NamedParameterInterface;
use Composer\Autoload\ClassLoader;
use RuntimeException;

use function assert;
use function file_exists;
use function is_int;
use function memory_get_peak_usage;
use function microtime;
use function number_format;
use function realpath;
use function spl_autoload_functions;
use function spl_autoload_register;
use function spl_autoload_unregister;
use function strpos;

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
    private Meta $appMeta;
    private CompileAutoload $dumpAutoload;
    private CompilePreload $compilePreload;
    private CompileObjectGraph $compilerObjectGraph;

    /**
     * @param AppName $appName application name "MyVendor|MyProject"
     * @param Context $context application context "prod-app"
     * @param AppDir  $appDir  application path
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
     * @return CompileReport
     */
    public function compile(): array
    {
        $preload = ($this->compilePreload)($this->appMeta, $this->context);
        $module = (new Module())($this->appMeta, $this->context);
        $compiler = new \Ray\Compiler\Compiler();
        $appDirRealpath = realpath($this->appMeta->appDir);
        assert($appDirRealpath !== false);
        $scriptDir = $appDirRealpath . '/var/di/' . $this->context;
        $compiler->compile($module, $scriptDir);

        // Compile class meta info (annotations and named parameters)
        $compiled = $this->compileClassMetaInfo();

        $dot = ($this->compilerObjectGraph)($module);
        $start = $_SERVER['REQUEST_TIME_FLOAT'] ?? 0.0;
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

    private function compileClassMetaInfo(): int
    {
        $injector = Injector::getInstance($this->appMeta->name, $this->context, $this->appMeta->appDir);
        $namedParams = $injector->getInstance(NamedParameterInterface::class);
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
