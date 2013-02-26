<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\Application;

use Ray\Di\Injector;
use Aura\Di\Exception;
use BEAR\Package\Provide\Application\DiLogger;
use Doctrine\Common\Cache\Cache;
use Ray\Di\AbstractModule;
use Ray\Di\Container;
use Ray\Di\Forge;
use Ray\Di\Config;
use Ray\Di\Annotation;
use Ray\Di\Definition;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\CachedReader;
use BEAR\Package\Provide\Application\Exception\InvalidMode;
use Ray\Di\Exception\Exception as DiException;

/**
 * Application object factory
 */
class ApplicationFactory
{
    /**
     * @param \Doctrine\Common\Cache\Cache $cache
     */
    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Return application instance
     *
     * @param string $appName application name
     * @param string $mode    run mode
     *
     * @return \BEAR\Sunday\Extension\Application\AppInterface
     * @throws InvalidMode
     */
    public function newInstance($appName, $mode)
    {
        $appKey = PHP_SAPI . $appName . $mode;
        $app = $this->cache->fetch($appKey);
        if ($app) {
            return $app;
        }
        $moduleName = $appName . '\Module\\' . $mode . 'Module';
        if (!class_exists($moduleName)) {
            throw new InvalidMode("Invalid mode [{$mode}], [$moduleName] class unavailable");
        }
        $injector = (new Injector(
                        new Container(
                            new Forge(
                                new Config(
                                    new Annotation(
                                        new Definition,
                                        new CachedReader(
                                            new AnnotationReader,
                                            $this->cache
                                        )
                                    )
                                )
                            )
                        ),
                        new $moduleName)
        )->setCache($this->cache);
        $diLogger = $injector->getInstance('BEAR\Package\Provide\Application\DiLogger');
        $injector->setLogger($diLogger);

        $app = $injector->getInstance('BEAR\Sunday\Extension\Application\AppInterface');
        /** @var $app \BEAR\Sunday\Extension\Application\AppInterface */
        $this->cache->save($appKey, $app);

        // log
        try {
            $logger = $injector->getInstance('Guzzle\Log\LogAdapterInterface');
            /** @var $logger \Guzzle\Log\LogAdapterInterface */
            $logger->log((string)$diLogger, LOG_INFO);
        } catch (DiException $e) {
            error_log((string)$diLogger, LOG_INFO);
        }

        return $app;
    }
}
