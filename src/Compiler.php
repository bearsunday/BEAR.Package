<?php

declare(strict_types=1);

namespace BEAR\Package;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\AppMeta\AppMeta;
use BEAR\Package\Provide\Error\NullPage;
use BEAR\Resource\Exception\ParameterException;
use BEAR\Resource\NamedParameterInterface;
use BEAR\Resource\Uri;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Cache\Cache;
use Ray\Di\AbstractModule;
use Ray\Di\Bind;
use Ray\Di\InjectorInterface;

final class Compiler
{
    private $classes = [];

    private $files = [];

    /**
     * Compile application
     *
     * @param string $appName application name "MyVendor|MyProject"
     * @param string $context application context "prod-app"
     * @param string $appDir  application path
     */
    public function __invoke(string $appName, string $context, string $appDir) : string
    {
        $loader = $this->compileLoader($appName, $context, $appDir);
        $log = $this->compileDiScripts($appName, $context, $appDir);

        return sprintf("Compile Log: %s\nautload.php: %s", $log, $loader);
    }

    public function compileDiScripts(string $appName, string $context, string $appDir) : string
    {
        $appMeta = new AppMeta($appName, $context, $appDir);
        (new Unlink)->force($appMeta->tmpDir);
        $cacheNs = (string) filemtime($appMeta->appDir . '/src');
        $injector = new AppInjector($appName, $context, $appMeta, $cacheNs);
        $cache = $injector->getInstance(Cache::class);
        $reader = $injector->getInstance(AnnotationReader::class);
        /* @var $reader \Doctrine\Common\Annotations\Reader */
        $namedParams = $injector->getInstance(NamedParameterInterface::class);
        /* @var $namedParams NamedParameterInterface */

        // create DI factory class and AOP compiled class for all resources and save $app cache.
        (new Bootstrap)->newApp($appMeta, $context, $cache);

        // check resource injection and create annotation cache
        foreach ($appMeta->getResourceListGenerator() as list($className)) {
            $this->scanClass($injector, $reader, $namedParams, $className);
        }
        $logFile = realpath($appMeta->logDir) . '/compile.log';
        $this->saveCompileLog($appMeta, $context, $logFile);

        return $logFile;
    }

    private function compileLoader(string $appName, string $context, string $appDir) : string
    {
        $loaderFile = $appDir . '/vendor/autoload.php';
        if (! file_exists($loaderFile)) {
            return '';
        }
        $loaderFile = require $loaderFile;
        spl_autoload_register(
            function ($class) use ($loaderFile) {
                $loaderFile->loadClass($class);
                if ($class !== NullPage::class) {
                    $this->classes[] = $class;
                }
            },
            false,
            true
        );

        $this->invokeTypicalReuqest($appName, $context);
        $fies = '<?php declare(strict_types=1);' . PHP_EOL;
        foreach ($this->classes as $class) {
            $isAutoloadFailed = ! class_exists($class, false) && ! interface_exists($class, false) && ! trait_exists($class, false); // could be phpdoc tag by anotation loader
            if ($isAutoloadFailed) {
                continue;
            }
            $fies .= sprintf(
                "require %s';\n",
                $this->getRelativePath($appDir, (new \ReflectionClass($class))->getFileName())
            );
        }
        $fies .= "require __DIR__ . '/vendor/autoload.php';" . PHP_EOL;
        $loaderFile = realpath($appDir) . '/autoload.php';
        file_put_contents($loaderFile, $fies);

        return $loaderFile;
    }

    private function getRelativePath(string $rootDir, string $file)
    {
        $dir = realpath($rootDir);
        if (strpos($file, $dir) !== false) {
            return str_replace("{$dir}", "__DIR__ . '", $file);
        }

        return "'" . $file;
    }

    private function invokeTypicalReuqest(string $appName, string $context)
    {
        $app = (new Bootstrap)->getApp($appName, $context);
        $ro = new NullPage;
        $ro->uri = new Uri('app://self/');
        $app->resource->get->object($ro)();
    }

    private function scanClass(InjectorInterface $injector, Reader $reader, NamedParameterInterface $namedParams, string $className)
    {
        try {
            $instance = $injector->getInstance($className);
        } catch (\Exception $e) {
            error_log(sprintf('Failed to instantiate [%s]: %s(%s)', $className, get_class($e), $e->getMessage()));

            return;
        }
        $class = new \ReflectionClass($className);
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

    private function isMagicMethod($method) : bool
    {
        return \in_array($method, ['__sleep', '__wakeup', 'offsetGet', 'offsetSet', 'offsetExists', 'offsetUnset', 'count', 'ksort', 'asort', 'jsonSerialize'], true);
    }

    private function saveNamedParam(NamedParameterInterface $namedParameter, $instance, string $method)
    {
        // named parameter
        if (! \in_array($method, ['onGet', 'onPost', 'onPut', 'onPatch', 'onDelete', 'onHead'], true)) {
            return;
        }
        try {
            $namedParameter->getParameters([$instance, $method], []);
        } catch (ParameterException $e) {
            return;
        }
    }

    private function saveCompileLog(AbstractAppMeta $appMeta, string $context, string $logFile)
    {
        $module = (new Module)($appMeta, $context);
        /** @var AbstractModule $module */
        $container = $module->getContainer();
        foreach ($appMeta->getResourceListGenerator() as list($class)) {
            new Bind($container, $class);
        }
        file_put_contents($logFile, (string) $module);
    }
}
