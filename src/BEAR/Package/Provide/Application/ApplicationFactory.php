<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\Application;

use Ray\Di\Injector;
use Ray\Di\AbstractModule;
use Ray\Di\Container;
use Ray\Di\Forge;
use Ray\Di\ApcConfig;
use Ray\Di\Annotation;
use Ray\Di\Definition;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use BEAR\Package\Provide\Application\Exception\InvalidMode;

class ApplicationFactory
{
    /**
     * Set loader
     *
     *  - set composer auto loader
     *  - set silent auto loader for doctrine annotation
     *  - set ignore annotation
     *
     * @param $packageDir
     *
     * @return ApplicationFactory
     */
    public function setLoader($packageDir)
    {
        AnnotationRegistry::registerAutoloadNamespace(__NAMESPACE__ . '\Annotation\\', dirname(dirname(__DIR__)));
        AnnotationRegistry::registerAutoloadNamespace('Ray\Di\Di\\', $packageDir . '/vendor/ray/di/src/');
        AnnotationRegistry::registerAutoloadNamespace(
            'BEAR\Resource\Annotation\\',
            $packageDir . '/vendor/bear/resource/src/'
        );
        AnnotationRegistry::registerAutoloadNamespace(
            'BEAR\Sunday\Annotation\\',
            $packageDir . '/vendor/bear/sunday/src/'
        );
        AnnotationReader::addGlobalIgnoredName('noinspection');
        AnnotationReader::addGlobalIgnoredName('returns'); // for Mr.Smarty. :(

        return $this;
    }

    /**
     * Return application instance
     *
     * @param string $appName application name
     * @param string $mode    run mode
     *
     * @return \BEAR\Sunday\Extension\Application\AppInterface
     * @throws Exception\InvalidMode
     */
    public function newInstance($appName, $mode)
    {
        $moduleName = $appName . '\Module\\' . $mode . 'Module';
        if (!class_exists($moduleName)) {
            throw new InvalidMode("Invalid mode [{$mode}], [$moduleName] class unavailable");
        }

        // create application instance
        $injector = new Injector(new Container(new Forge(new ApcConfig(new Annotation(new Definition, new AnnotationReader)))), new $moduleName);
        $app = $injector->getInstance('BEAR\Sunday\Extension\Application\AppInterface');

        return $app;
    }
}
