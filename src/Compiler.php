<?php

declare(strict_types=1);

namespace BEAR\Package;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\AppMeta\Meta;
use BEAR\Package\Provide\Error\NullPage;
use BEAR\Resource\Exception\ParameterException;
use BEAR\Resource\NamedParameterInterface;
use BEAR\Resource\Uri;
use BEAR\Sunday\Extension\Application\AbstractApp;
use BEAR\Sunday\Extension\Application\AppInterface;
use Composer\Autoload\ClassLoader;
use Doctrine\Common\Annotations\Reader;
use Exception;
use function file_exists;
use function file_put_contents;
use function in_array;
use const PHP_EOL;
use function printf;
use Ray\Di\AbstractModule;
use Ray\Di\Exception\Unbound;
use Ray\Di\InjectorInterface;
use Ray\ObjectGrapher\ObjectGrapher;
use function realpath;
use ReflectionClass;
use RuntimeException;
use function sprintf;

final class Compiler
{
    /**
     * @var string[]
     */
    private $classes = [];

    /**
     * @var InjectorInterface
     */
    private $injector;

    /**
     * @var string
     */
    private $appName;

    /**
     * @var string
     */
    private $context;

    /**
     * @var string
     */
    private $appDir;

    /**
     * @var Meta
     */
    private $appMeta;

    /**
     * @var array<int, string>
     */
    private $compiled = [];

    /**
     * @var array<string, string>
     */
    private $failed = [];

    /**
     * @var list<string>
     */
    private $overwritten = [];

    /**
     * @param string $appName application name "MyVendor|MyProject"
     * @param string $context application context "prod-app"
     * @param string $appDir  application path
     */
    public function __construct(string $appName, string $context, string $appDir)
    {
        $this->registerLoader($appDir);
        $this->hookNullObjectClass($appDir);
        $this->appName = $appName;
        $this->context = $context;
        $this->appDir = $appDir;
        $this->appMeta = new Meta($appName, $context, $appDir);
        /** @psalm-suppress MixedAssignment */
        $this->injector = Injector::getInstance($appName, $context, $appDir);
    }

    /**
     * Compile application
     */
    public function compile() : int
    {
        if (! is_dir($this->appDir)) {
            throw new RuntimeException($this->appDir);
        }
        $preload = $this->compilePreload($this->appMeta, $this->context);
        $module = (new Module)($this->appMeta, $this->context);
        $this->compileSrc($module);
        echo PHP_EOL;
        $this->compileDiScripts($this->appMeta);
        $dot = $this->compileObjectGraphDotFile($module);
        /** @var float $start */
        $start = $_SERVER['REQUEST_TIME_FLOAT'];
        $time = number_format(microtime(true) - $start, 2);
        $memory = number_format(memory_get_peak_usage() / (1024 * 1024), 3);
        echo PHP_EOL;
        printf("Compilation (1/2) took %f seconds and used %fMB of memory\n", $time, $memory);
        printf("Success: %d Failed: %d\n", count($this->compiled), count($this->failed));
        printf("preload.php: %s\n", $this->getFileInfo($preload));
        printf("module.dot: %s\n", $this->getFileInfo($dot));

        foreach ($this->failed as $depedencyIndex => $error) {
            printf("UNBOUND: %s for %s \n", $error, $depedencyIndex);
        }

        return $this->failed ? 1 : 0;
    }

    public function dumpAutoload() : int
    {
        echo PHP_EOL;
        $this->invokeTypicalRequest();
        $paths = $this->getPaths($this->classes);
        $autolaod = $this->saveAutoloadFile($this->appMeta->appDir, $paths);
        /** @var float $start */
        $start = $_SERVER['REQUEST_TIME_FLOAT'];
        $time = number_format(microtime(true) - $start, 2);
        $memory = number_format(memory_get_peak_usage() / (1024 * 1024), 3);
        printf("Compilation (2/2) took %f seconds and used %fMB of memory\n", $time, $memory);
        printf("autoload.php: %s\n", $this->getFileInfo($autolaod));

        return 0;
    }

    public function registerLoader(string $appDir) : void
    {
        $loaderFile = $appDir . '/vendor/autoload.php';
        if (! file_exists($loaderFile)) {
            throw new RuntimeException('no loader');
        }
        /** @var ClassLoader $loader */
        $loader = require $loaderFile;
        spl_autoload_register(
            /** @var class-string $class */
            function (string $class) use ($loader) : void {
                $loader->loadClass($class);
                if ($class !== NullPage::class) {
                    $this->classes[] = $class;
                }
            },
            false,
            true
        );
    }

    public function compileDiScripts(AbstractAppMeta $appMeta) : void
    {
        $reader = $this->injector->getInstance(Reader::class);
        assert($reader instanceof Reader);
        $namedParams = $this->injector->getInstance(NamedParameterInterface::class);
        assert($namedParams instanceof NamedParameterInterface);
        // create DI factory class and AOP compiled class for all resources and save $app cache.
        $app = $this->injector->getInstance(AppInterface::class);
        assert($app instanceof AppInterface);

        // check resource injection and create annotation cache
        $metas = $appMeta->getResourceListGenerator();
        /** @var array{0: string, 1:string} $meta */
        foreach ($metas as $meta) {
            /** @var string $className */
            [$className] = $meta;
            assert(class_exists($className));
            $this->scanClass($reader, $namedParams, $className);
        }
    }

    public function compileSrc(AbstractModule $module) : AbstractModule
    {
        $container = $module->getContainer()->getContainer();
        $dependencies = array_keys($container);
        sort($dependencies);
        foreach ($dependencies as $dependencyIndex) {
            $pos = strpos((string) $dependencyIndex, '-');
            assert(is_int($pos));
            $interface = substr((string) $dependencyIndex, 0, $pos);
            $name = substr((string) $dependencyIndex, $pos + 1);
            $this->getInstance($interface, $name);
        }

        return $module;
    }

    private function getFileInfo(string $filename) : string
    {
        if (in_array($filename, $this->overwritten, true)) {
            return $filename . ' (overwritten)';
        }

        return $filename;
    }

    /**
     * @param array<string> $paths
     */
    private function saveAutoloadFile(string $appDir, array $paths) : string
    {
        $requiredFile = '';
        foreach ($paths as $path) {
            $requiredFile .= sprintf(
                "require %s';\n",
                $this->getRelativePath($appDir, $path)
            );
        }
        $autoloadFile = sprintf("<?php

// %s autoload

%s
require __DIR__ . '/vendor/autoload.php';
", $this->context, $requiredFile);
        $fileName = realpath($appDir) . '/autoload.php';
        $this->putFileContents($fileName, $autoloadFile);

        return $fileName;
    }

    private function compilePreload(AbstractAppMeta $appMeta, string $context) : string
    {
        $this->loadResources($appMeta->name, $context, $appMeta->appDir);
        $paths = $this->getPaths($this->classes);
        $requiredOnceFile = '';
        foreach ($paths as $path) {
            $requiredOnceFile .= sprintf(
                "require_once %s';\n",
                $this->getRelativePath($appMeta->appDir, $path)
            );
        }
        $preloadFile = sprintf("<?php

// %s preload

require __DIR__ . '/vendor/autoload.php';

%s", $this->context, $requiredOnceFile);
        $fileName = realpath($appMeta->appDir) . '/preload.php';
        $this->putFileContents($fileName, $preloadFile);

        return $fileName;
    }

    private function getRelativePath(string $rootDir, string $file) : string
    {
        $dir = (string) realpath($rootDir);
        if (strpos($file, $dir) !== false) {
            return (string) preg_replace('#^' . preg_quote($dir, '#') . '#', "__DIR__ . '", $file);
        }

        return $file;
    }

    /**
     * @psalm-suppress MixedFunctionCall
     * @psalm-suppress NoInterfaceProperties
     */
    private function invokeTypicalRequest() : void
    {
        $app = $this->injector->getInstance(AppInterface::class);
        assert($app instanceof AbstractApp);
        $ro = new NullPage;
        $ro->uri = new Uri('app://self/');
        /** @psalm-suppress MixedMethodCall */
        $app->resource->get->object($ro)();
    }

    /**
     * Save annotation and method meta information
     *
     * @template T
     *
     * @param class-string<T> $className
     */
    private function scanClass(Reader $reader, NamedParameterInterface $namedParams, string $className) : void
    {
        $class = new ReflectionClass($className);
        /** @var T $instance */
        $instance = $class->newInstanceWithoutConstructor();
        if (! $instance instanceof $className) {
            return;
        }
        $reader->getClassAnnotations($class);
        $methods = $class->getMethods();
        $log = sprintf('M %s:', $className);
        foreach ($methods as $method) {
            $methodName = $method->getName();
            if ($this->isMagicMethod($methodName)) {
                continue;
            }
            if (substr($methodName, 0, 2) === 'on') {
                $log .= sprintf(' %s', $methodName);
                $this->saveNamedParam($namedParams, $instance, $methodName);
            }
            // method annotation
            $reader->getMethodAnnotations($method);
            $log .= sprintf('@ %s', $methodName);
        }
//        echo $log . PHP_EOL;
    }

    private function isMagicMethod(string $method) : bool
    {
        return in_array($method, ['__sleep', '__wakeup', 'offsetGet', 'offsetSet', 'offsetExists', 'offsetUnset', 'count', 'ksort', 'asort', 'jsonSerialize'], true);
    }

    private function saveNamedParam(NamedParameterInterface $namedParameter, object $instance, string $method) : void
    {
        // named parameter
        if (! in_array($method, ['onGet', 'onPost', 'onPut', 'onPatch', 'onDelete', 'onHead'], true)) {
            return;
        }
        $callable = [$instance, $method];
        if (! is_callable($callable)) {
            return;
        }
        try {
            $namedParameter->getParameters($callable, []);
        } catch (ParameterException $e) {
            return;
        }
    }

    /**
     * @param array<string> $classes
     *
     * @return array<string>
     */
    private function getPaths(array $classes) : array
    {
        $paths = [];
        foreach ($classes as $class) {
            // could be phpdoc tag by annotation loader
            $isAutoloadFailed = ! class_exists($class, false) && ! interface_exists($class, false) && ! trait_exists($class, false);
            if ($isAutoloadFailed) {
                continue;
            }
            assert(class_exists($class) || interface_exists($class) || trait_exists($class));
            $filePath = (string) (new ReflectionClass($class))->getFileName();
            if (! file_exists($filePath) || strpos($filePath, 'phar') === 0) {
                continue;
            }
            $paths[] = $this->getRelativePath($this->appDir, $filePath);
        }

        return $paths;
    }

    private function loadResources(string $appName, string $context, string $appDir) : void
    {
        $meta = new Meta($appName, $context, $appDir);
        $resMetas = $meta->getGenerator('*');
        foreach ($resMetas as $resMeta) {
            $this->getInstance($resMeta->class);
        }
    }

    private function getInstance(string $interface, string $name = '') : void
    {
        $dependencyIndex = $interface . '-' . $name;
        if (in_array($dependencyIndex, $this->compiled, true)) {
            printf("S %s:%s\n", $interface, $name);

            return;
        }
        try {
            $this->injector->getInstance($interface, $name);
            $this->compiled[] = $dependencyIndex;
            $this->progress('.');
        } catch (Unbound $e) {
            if ($dependencyIndex === 'Ray\Aop\MethodInvocation-') {
                return;
            }
            $this->failed[$dependencyIndex] = $e->getMessage();
            $this->progress('F');
        } catch (Exception $e) {
            $this->failed[$dependencyIndex] = sprintf('%s: %s', get_class($e), $e->getMessage());
            $this->progress('F');
        }
    }

    private function progress(string $char) : void
    {
        /**
         * @var int
         */
        static $cnt = 0;

        echo $char;
        $cnt++;
        if ($cnt === 60) {
            $cnt = 0;
            echo PHP_EOL;
        }
    }

    private function hookNullObjectClass(string $appDir) : void
    {
        $compileScript = realpath($appDir) . '/.compile.php';
        if (file_exists($compileScript)) {
            require $compileScript;
        }
    }

    private function putFileContents(string $fileName, string $content) : void
    {
        if (file_exists($fileName)) {
            $this->overwritten[] = $fileName;
        }
        file_put_contents($fileName, $content);
    }

    private function compileObjectGraphDotFile(AbstractModule $module) : string
    {
        $dotFile = sprintf('%s/module.dot', $this->appDir);
        $this->putFileContents($dotFile, (new ObjectGrapher)($module));

        return $dotFile;
    }
}
