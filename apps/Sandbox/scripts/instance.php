<?php
/**
 * Application instance script
 *
 * @return $app  \BEAR\Sunday\Extension\Application\AppInterface
 * @global $mode string configuration mode
 */
namespace Sandbox;

use BEAR\Package\Provide\Application\DiLogger;
use Doctrine\Common\Cache\ApcCache;
use Doctrine\Common\Cache\FilesystemCache;
use Ray\Di\Injector;

require_once __DIR__ . '/bootstrap.php';

// mode
$mode = isset($mode) ? $mode : 'Prod';

$cache = function_exists('apc_fetch') ? new ApcCache : new FilesystemCache(dirname(__DIR__) . '/data/tmp/cache');
$appKey = __NAMESPACE__ . $mode;

// return cached application
if ($cache->contains($appKey)) {
    return $cache->fetch($appKey);
}

// new application instance
$diLogger = new DiLogger;
$injector = Injector::create([new Module\AppModule($mode)], $cache)->setLogger($diLogger);
$app = $injector->getInstance('\BEAR\Sunday\Extension\Application\AppInterface');
$cache->save($appKey, $app);

// di log
file_put_contents(dirname(__DIR__) . '/data/log/di.log', (string)$injector . (string)$diLogger);

return $app;
