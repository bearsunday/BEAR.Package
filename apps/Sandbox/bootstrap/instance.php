<?php
/**
 * Application instance script
 *
 * @return $app  \BEAR\Sunday\Extension\Application\AppInterface
 * @global $context string configuration mode
 */
namespace Sandbox;

use BEAR\Package\Dev\Application\ApplicationReflector;
use BEAR\Package\Provide\Application\AbstractApp;
use Doctrine\Common\Cache\ApcCache;
use Doctrine\Common\Cache\FilesystemCache;
use Ray\Di\CacheInjector;
use Ray\Di\Injector;
use BEAR\Sunday\Extension\Application\AppInterface;

require_once __DIR__ . '/autoload.php';

// mode
$context = isset($context) ? $context : 'prod';

//
// return application injector
//
$injector = function () use ($context) {
    return Injector::create([new Module\AppModule($context)]);
};

//
// post injection procedure, this was called only one time in system startup.
//
$initialization = function (AbstractApp $app) use ($context) {
    $diLog = (string)$app->injector . PHP_EOL . (string)$app->injector->getLogger();
    file_put_contents(dirname(__DIR__) . '/var/log/boot.log', $diLog);
    if ($context === 'prod') {
        (new ApplicationReflector($app))->compileAllResources();
    }
};

//
// get application instance with cache key
//
$injector = new CacheInjector($injector, $initialization, __NAMESPACE__ . $context, new ApcCache);
$app = $injector->getInstance('\BEAR\Sunday\Extension\Application\AppInterface');

/* @var $app \BEAR\Sunday\Extension\Application\AppInterface */
return $app;
