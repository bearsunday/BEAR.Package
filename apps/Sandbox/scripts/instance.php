<?php
/**
 * Application instance script
 *
 *  set auto loader
 *  create application object using apc cache
 *
 * @return $app  application
 * @global $mode configuration mode
 */
namespace Sandbox;

use Ray\Di\Injector;
use Ray\Di\Container;
use Ray\Di\Forge;
use Ray\Di\ApcConfig;
use Ray\Di\Annotation;
use Ray\Di\Definition;
use Doctrine\Common\Annotations\AnnotationReader;
use BEAR\Sunday\Output\Console;
use BEAR\Sunday\Web\SymfonyResponse;
use BEAR\Sunday\Exception\ExceptionHandler;

// init
umask(0);

// mode
$mode = isset($mode) ? $mode : 'Prod';

// cached application ?
$cacheKey = __NAMESPACE__ . PHP_SAPI . $mode;

// load
require_once __DIR__ . '/load.php';
$app = apc_fetch($cacheKey);
if ($app) {
    return $app;
}
// create app object with  "run-mode" module
$moduleName = __NAMESPACE__ . '\Module\\' . $mode . 'Module';

$module = new $moduleName;
$injector = new Injector(new Container(new Forge(new ApcConfig(new Annotation(new Definition, new AnnotationReader)))), new $module);
// log binding info
file_put_contents(dirname(__DIR__) . "/data/log/module.{$cacheKey}.log", (string)$injector);
try {
    $app = $injector->getInstance('BEAR\Sunday\Application\Context');
} catch (\Exception $e) {
    $handler = new ExceptionHandler;
    $handler->setLogDir(dirname(__DIR__) . '/data/log');
    $handler->setResponse(new SymfonyResponse(new Console));
    $page = $handler->handle($e);
    echo $page;
    exit(1);
}
apc_store($cacheKey, $app);

return $app;
