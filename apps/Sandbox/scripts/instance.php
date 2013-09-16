<?php
/**
 * Application instance script
 *
 * @return $app  \BEAR\Sunday\Extension\Application\AppInterface
 * @global $mode string configuration mode
 */
namespace Sandbox;

use BEAR\Package\Dev\Application\ApplicationReflector;
use Doctrine\Common\Cache\ApcCache;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\FilesystemCache;
use Ray\Di\CacheInjector;
use Ray\Di\Injector;
use BEAR\Sunday\Extension\Application\AppInterface;

require_once __DIR__ . '/bootstrap.php';

// mode
$mode = isset($mode) ? $mode : 'prod';

//
// return application injector
//
$injector = function() use ($mode) {
    return Injector::create([new Module\AppModule($mode)]);
};

//
// post injection procedure, this was called only one time in system startup.
//
$postInject = function(AppInterface $app){
    (new ApplicationReflector($app))->compileAllResources();
};

//
// get application instance with cache key
//
$injector = new CacheInjector($injector, $postInject, __NAMESPACE__ . $mode, new ArrayCache);
$app = $injector->getInstance('\BEAR\Sunday\Extension\Application\AppInterface');

/* @var $app \BEAR\Sunday\Extension\Application\AppInterface */
return $app;
