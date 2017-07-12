<?php
/**
 * This file is part of the BEAR.Package package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Package;

use BEAR\AppMeta\AppMeta;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\Cache;

final class Compiler
{
    /**
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

        // create DI factory class and AOP compiled class for all resources and save $app cache.
        (new Bootstrap)->newApp($appMeta, $context, $cache);

        // check resource injection and create annotation cache
        foreach ($appMeta->getResourceListGenerator() as list($class)) {
            $injector->getInstance($class);
            $refClass = new \ReflectionClass($class);
            $reader->getClassAnnotations($refClass);
            $methods = (new \ReflectionClass($refClass))->getMethods();
            foreach ($methods as $method) {
                $reader->getMethodAnnotations($method);
            }
        }
    }
}
