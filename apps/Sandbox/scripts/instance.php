<?php
/**
 * Application instance script
 *
 * @return $app  \BEAR\Sunday\Extension\Application\AppInterface
 * @global $mode string configuration mode
 */
namespace Sandbox;

use BEAR\Package\Dev\Application\ApplicationReflector;
use BEAR\Package\Provide\Application\DiLogger;
use Doctrine\Common\Cache\ApcCache;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\FilesystemCache;
use Ray\Di\CacheInjector;
use Ray\Di\Injector;
use Ray\Di\InjectorInterface;
use BEAR\Sunday\Extension\Application\AppInterface;

require_once __DIR__ . '/bootstrap.php';

// mode
$mode = isset($mode) ? $mode : 'prod';

$module = function() use ($mode) {return new Module\AppModule($mode);};
$init = function(InjectorInterface $injector, AppInterface $app) {
    (new ApplicationReflector($app))->compileAllResources();
    file_put_contents(dirname(__DIR__) . '/data/log/di.log', (string)$injector);
};
$injector = new CacheInjector($module, dirname(__DIR__) . '/data/aop', new ApcCache);
$logger = function(){ return new DiLogger;};
$cache = function_exists('apc_fetch') ? new ApcCache : new FilesystemCache(dirname(__DIR__) . '/data/tmp');
$cache->setNamespace(__NAMESPACE__);
$injector = new CacheInjector($module, dirname(__DIR__) . '/data/aop', $cache, $logger);
$app = $injector->setInit($init)->getInstance('\BEAR\Sunday\Extension\Application\AppInterface');
/* @var $app \BEAR\Sunday\Extension\Application\AppInterface */

return $app;
