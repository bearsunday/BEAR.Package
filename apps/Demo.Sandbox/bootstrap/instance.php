<?php
/**
 * Application instance script
 *
 * @return $app \BEAR\Sunday\Extension\Application\AppInterface
 *
 * @global $context string application context
 */
namespace Demo\Sandbox;

use BEAR\Package\Bootstrap\Bootstrap;

require_once __DIR__ . '/autoload.php';

// Hierarchical profiler @see http://www.php.net/manual/en/book.xhprof.php
// \BEAR\Package\Dev\Debug\Debug::xhprof();

$app =  Bootstrap::getApp(
    __NAMESPACE__,
    isset($context) ? $context : 'prod',
    dirname(__DIR__) . '/var/tmp'
);

return $app;
