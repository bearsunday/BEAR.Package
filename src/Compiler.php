<?php

declare(strict_types=1);

namespace BEAR\Package;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\AppMeta\Meta;
use BEAR\Package\Provide\Error\NullPage;
use BEAR\Resource\Exception\ParameterException;
use BEAR\Resource\NamedParameterInterface;
use BEAR\Resource\Uri;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Cache\Cache;
use function file_exists;
use Ray\Di\AbstractModule;
use Ray\Di\Bind;
use Ray\Di\InjectorInterface;
use ReflectionClass;

final class Compiler
{
    /**
     * @var string[]
     */
    private $classes = [];

    /**
     * @var string
     */
    private $ns;

    /**
     * Compile application
     *
     * @param string $appName application name "MyVendor|MyProject"
     * @param string $context application context "prod-app"
     * @param string $appDir  application path
     */
    public function __invoke(string $appName, string $context, string $appDir) : string
    {
        $this->registerLoader($appDir);
        $autoload = $this->compileAutoload($appName, $context, $appDir);
        $preload = $this->compilePreload($appDir);
        $log = $this->compileDiScripts($appName, $context, $appDir);
        $this->ns = (string) filemtime(realpath($appDir) . '/src');

        return sprintf("Compile Log: %s\nautoload.php: %s\npreload.php: %s", $log, $autoload, $preload);
    }

    public function registerLoader(string $appDir) : void
    {
        $loaderFile = $appDir . '/vendor/autoload.php';
        if (! file_exists($loaderFile)) {
            throw new \RuntimeException('no loader');
        }
        $loaderFile = require $loaderFile;
        spl_autoload_register(
            function ($class) use ($loaderFile) : void {
                $loaderFile->loadClass($class);
                if ($class !== NullPage::class) {
                    $this->classes[] = $class;
                }
            },
            false,
            true
        );
    }

    public function compileDiScripts(string $appName, string $context, string $appDir) : string
    {
        $appMeta = new Meta($appName, $context, $appDir);
        $injector = new AppInjector($appName, $context, $appMeta, $this->ns);
        $cache = $injector->getInstance(Cache::class);
        $reader = $injector->getInstance(AnnotationReader::class);
        /* @var $reader \Doctrine\Common\Annotations\Reader */
        $namedParams = $injector->getInstance(NamedParameterInterface::class);
        /* @var $namedParams NamedParameterInterface */

        // create DI factory class and AOP compiled class for all resources and save $app cache.
        (new Bootstrap)->newApp($appMeta, $context, $cache);

        // check resource injection and create annotation cache
        foreach ($appMeta->getResourceListGenerator() as [$className]) {
            $this->scanClass($injector, $reader, $namedParams, (string) $className);
        }
        $logFile = realpath($appMeta->logDir) . '/compile.log';
        $this->saveCompileLog($appMeta, $context, $logFile);

        return $logFile;
    }

    private function compileAutoload(string $appName, string $context, string $appDir) : string
    {
        $this->invokeTypicalRequest($appName, $context);
        $paths = $this->getPaths($this->classes, $appDir);

        return $this->dumpAutoload($appDir, $paths);
    }

    private function dumpAutoload(string $appDir, array $paths) : string
    {
        $autoloadFile = '<?php' . PHP_EOL;
        foreach ($paths as $path) {
            $autoloadFile .= sprintf(
                "require %s';\n",
                $this->getRelativePath($appDir, $path)
            );
        }
        $autoloadFile .= "require __DIR__ . '/vendor/autoload.php';" . PHP_EOL;
        $loaderFile = realpath($appDir) . '/autoload.php';
        file_put_contents($loaderFile, $autoloadFile);

        return $loaderFile;
    }

    private function compilePreload(string $appDir) : string
    {
        $paths = $this->getPaths($this->classes, $appDir);
        $output = '<?php' . PHP_EOL;
        $output .= "opcache_compile_file(__DIR__ . '/vendor/autoload.php');" . PHP_EOL;
        foreach ($paths as $path) {
            $output .= sprintf(
                "opcache_compile_file(%s');\n",
                $this->getRelativePath($appDir, $path)
            );
        }
        $preloadFile = realpath($appDir) . '/preload.php';
        file_put_contents($preloadFile, $output);

        return $preloadFile;
    }

    private function getRelativePath(string $rootDir, string $file) : string
    {
        $dir = (string) realpath($rootDir);
        if (strpos($file, $dir) !== false) {
            return (string) preg_replace('#^' . preg_quote($dir, '#') . '#', "__DIR__ . '", $file);
        }

        return $file;
    }

    private function invokeTypicalRequest(string $appName, string $context) : void
    {
        $app = (new Bootstrap)->getApp($appName, $context);
        $ro = new NullPage;
        $ro->uri = new Uri('app://self/');
        $app->resource->get->object($ro)();
    }

    private function scanClass(InjectorInterface $injector, Reader $reader, NamedParameterInterface $namedParams, string $className) : void
    {
        try {
            $instance = $injector->getInstance($className);
        } catch (\Exception $e) {
            error_log(sprintf('Failed to instantiate [%s]: %s(%s) in %s on line %s', $className, get_class($e), $e->getMessage(), $e->getFile(), $e->getLine()));

            return;
        }
        assert(class_exists($className));
        $class = new ReflectionClass($className);
        $reader->getClassAnnotations($class);
        $methods = $class->getMethods();
        foreach ($methods as $method) {
            $methodName = $method->getName();
            if ($this->isMagicMethod($methodName)) {
                continue;
            }
            $this->saveNamedParam($namedParams, $instance, $methodName);
            // method annotation
            $reader->getMethodAnnotations($method);
        }
    }

    private function isMagicMethod(string $method) : bool
    {
        return \in_array($method, ['__sleep', '__wakeup', 'offsetGet', 'offsetSet', 'offsetExists', 'offsetUnset', 'count', 'ksort', 'asort', 'jsonSerialize'], true);
    }

    private function saveNamedParam(NamedParameterInterface $namedParameter, object $instance, string $method) : void
    {
        // named parameter
        if (! \in_array($method, ['onGet', 'onPost', 'onPut', 'onPatch', 'onDelete', 'onHead'], true)) {
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

    private function saveCompileLog(AbstractAppMeta $appMeta, string $context, string $logFile) : void
    {
        $module = (new Module)($appMeta, $context);
        /** @var AbstractModule $module */
        $container = $module->getContainer();
        foreach ($appMeta->getResourceListGenerator() as [$class]) {
            new Bind($container, (string) $class);
        }
        file_put_contents($logFile, (string) $module);
    }

    private function getPaths(array $classes, string $appDir) : array
    {
        $paths = [];
        foreach ($classes as $class) {
            // could be phpdoc tag by annotation loader
            $isAutoloadFailed = ! class_exists($class, false) && ! interface_exists($class, false) && ! trait_exists($class, false);
            if ($isAutoloadFailed) {
                continue;
            }
            $filePath = (string) (new ReflectionClass($class))->getFileName();
            if (! file_exists($filePath) || strpos($filePath, 'phar') === 0) {
                continue;
            }
            $paths[] = $this->getRelativePath($appDir, $filePath);
        }

        return $paths;
    }
}
