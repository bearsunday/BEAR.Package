<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */

namespace BEAR\Bootstrap;

use BEAR\Package\Dev\Application\ApplicationReflector;
use BEAR\Package\Provide\Application\AbstractApp;
use Doctrine\Common\Cache\ApcCache;
use Doctrine\Common\Cache\FilesystemCache;
use Ray\Di\CacheInjector;
use Ray\Di\Injector;

/**
 * Return application instance
 * 
 * @param $appName
 * @param $context
 *
 * @return \BEAR\Sunday\Extension\Application\AppInterface
 */
function getApp($appName, $context)
{
    $injector = function () use ($appName, $context) {
        $appModule = "{$appName}\Module\AppModule";
        return Injector::create([new $appModule($context)]);
    };

    $initialization = function (AbstractApp $app) use ($context) {
        //$diLog = (string)$app->injector . PHP_EOL . (string)$app->injector->getLogger();
        if ($context === 'prod') {
            (new ApplicationReflector($app))->compileAllResources();
        }
    };
    $cache = function_exists('apc_fetch') ? new ApcCache : new FilesystemCache(sys_get_temp_dir());
    $injector = new CacheInjector($injector, $initialization, $appName . $context, $cache);
    $app = $injector->getInstance('\BEAR\Sunday\Extension\Application\AppInterface');

    /* @var $app \BEAR\Sunday\Extension\Application\AppInterface */
    return $app;
}
