<?php
/**
 * Application instance script
 *
 * +set auto loader
 * create application object using apc cache
 *
 * @return $app  application
 * @global $mode string configuration mode
 */
namespace Sandbox;

use Ray\Di\Injector;
use Ray\Di\AbstractModule;
use Ray\Di\Container;
use Ray\Di\Forge;
use Ray\Di\ApcConfig;
use Ray\Di\Annotation;
use Ray\Di\Definition;
use Doctrine\Common\Annotations\AnnotationReader;
use BEAR\Package\Exception\InvalidMode;

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
$moduleName = __NAMESPACE__ . '\Module\\' . $mode . 'Module';
if (!class_exists($moduleName)) {
    throw new InvalidMode("Invalid mode [{$mode}], check [$moduleName]");
}

// create application object
$injector = new Injector(new Container(new Forge(new ApcConfig(new Annotation(new Definition, new AnnotationReader)))), new $moduleName);
$app = $injector->getInstance('BEAR\Sunday\Application\Context');
// log binding info
$logFile = dirname(__DIR__) . "/data/log/module.{$cacheKey}.log";
file_put_contents($logFile, (string)$injector);

// store
apc_store($cacheKey, $app);
apc_store($cacheKey . '-injector', $injector);
return $app;