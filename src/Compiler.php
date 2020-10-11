<?php

declare(strict_types=1);

namespace BEAR\Package;

use ArrayObject;
use BEAR\AppMeta\Meta;
use BEAR\Package\Compiler\CompileAutoload;
use BEAR\Package\Compiler\CompileClassMetaInfo;
use BEAR\Package\Compiler\CompileDependencies;
use BEAR\Package\Compiler\CompileDiScripts;
use BEAR\Package\Compiler\CompileObjectGraph;
use BEAR\Package\Compiler\CompilePreload;
use BEAR\Package\Compiler\FilePutContents;
use BEAR\Package\Compiler\NewInstance;
use BEAR\Package\Provide\Error\NullPage;
use Composer\Autoload\ClassLoader;
use Ray\Di\InjectorInterface;
use RuntimeException;

use function assert;
use function count;
use function file_exists;
use function is_float;
use function memory_get_peak_usage;
use function microtime;
use function number_format;
use function printf;
use function realpath;
use function spl_autoload_register;

use const PHP_EOL;

final class Compiler
{
    /** @var list<string> */
    private $classes = [];

    /** @var InjectorInterface */
    private $injector;

    /** @var string */
    private $context;

    /** @var Meta */
    private $appMeta;

    /** @var CompileDiScripts */
    private $compilerDiScripts;

    /** @var NewInstance */
    private $newInstance;

    /** @var CompileAutoload */
    private $dumpAutoload;

    /** @var CompilePreload */
    private $compilePreload;

    /** @var CompileObjectGraph */
    private $compilerObjectGraph;

    /** @var CompileDependencies */
    private $compileDependencies;

    /**
     * @param string $appName application name "MyVendor|MyProject"
     * @param string $context application context "prod-app"
     * @param string $appDir  application path
     */
    public function __construct(string $appName, string $context, string $appDir)
    {
        $this->registerLoader($appDir);
        $this->hookNullObjectClass($appDir);
        $this->context = $context;
        $this->appMeta = new Meta($appName, $context, $appDir);
        /** @psalm-suppress MixedAssignment (?) */
        $this->injector = Injector::getInstance($appName, $context, $appDir);
        $this->compilerDiScripts = new CompileDiScripts(new CompileClassMetaInfo(), $this->injector);
        $this->newInstance = new NewInstance($this->injector);
        /** @var ArrayObject<int, string> $overWritten */
        $overWritten = new ArrayObject();
        /** @var ArrayObject<int, string> $classes */
        $classes = new ArrayObject();
        $filePutContents = new FilePutContents();
        $this->dumpAutoload = new CompileAutoload($this->injector, $filePutContents, $this->appMeta, $overWritten, $classes, $appDir, $context);
        $this->compilePreload = new CompilePreload($this->newInstance, $this->dumpAutoload, $filePutContents, $classes, $context);
        $this->compilerObjectGraph = new CompileObjectGraph($filePutContents, $appDir);
        $this->compileDependencies = new CompileDependencies($this->newInstance);
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
        ($this->compileDependencies)($module);
        echo PHP_EOL;
        ($this->compilerDiScripts)($this->appMeta);
        $failed = $this->newInstance->getFailed();
        $dot = $failed ? '' : ($this->compilerObjectGraph)($module);
        $start = $_SERVER['REQUEST_TIME_FLOAT'];
        assert(is_float($start));
        $time = number_format(microtime(true) - $start, 2);
        $memory = number_format(memory_get_peak_usage() / (1024 * 1024), 3);
        echo PHP_EOL;
        printf("Compilation (1/2) took %f seconds and used %fMB of memory\n", $time, $memory);
        printf("Success: %d Failed: %d\n", $this->newInstance->getCompiled(), count($this->newInstance->getFailed()));
        printf("preload.php: %s\n", $this->dumpAutoload->getFileInfo($preload));
        printf("module.dot: %s\n", $dot ? $this->dumpAutoload->getFileInfo($dot) : 'n/a');
        foreach ($this->newInstance->getFailed() as $depedencyIndex => $error) {
            printf("UNBOUND: %s for %s \n", $error, $depedencyIndex);
        }

        return $failed ? 1 : 0;
    }

    public function dumpAutoload(): int
    {
        return ($this->dumpAutoload)();
    }

    private function registerLoader(string $appDir): void
    {
        $loaderFile = $appDir . '/vendor/autoload.php';
        if (! file_exists($loaderFile)) {
            throw new RuntimeException('no loader');
        }

        $loader = require $loaderFile;
        assert($loader instanceof ClassLoader);
        spl_autoload_register(
            /** @var class-string $class */
            function (string $class) use ($loader): void {
                $loader->loadClass($class);
                if ($class !== NullPage::class) {
                    $this->classes[] = $class;
                }
            }
        );
    }

    private function hookNullObjectClass(string $appDir): void
    {
        $compileScript = realpath($appDir) . '/.compile.php';
        if (file_exists($compileScript)) {
            require $compileScript;
        }
    }
}
