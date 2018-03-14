<?php
/**
 * This file is part of the BEAR.Package package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Package;

use BEAR\AppMeta\AppMeta;
use BEAR\Resource\Exception\ParameterException;
use BEAR\Resource\NamedParameterInterface;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Cache\Cache;
use Ray\Di\InjectorInterface;

final class Compiler
{
    /**
     * Compile application
     *
     * @param string $appName application name "MyVendor|MyProject"
     * @param string $context application context "prod-app"
     * @param string $appDir  application path
     */
    public function __invoke($appName, $context, $appDir)
    {
        $appMeta = new AppMeta($appName, $context, $appDir);
        $injector = new AppInjector($appName, $context);
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
    }

    private function scanClass(InjectorInterface $injector, Reader $reader, NamedParameterInterface $namedParams, string $className)
    {
        $instance = $injector->getInstance($className);
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
}
