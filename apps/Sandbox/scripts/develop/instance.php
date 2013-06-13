<?php
/**
 * Return application instance with development setup
 *
 * @global $mode
 */

use BEAR\Package\Dev\Dev;
use BEAR\Package\Dev\Resource\ResourceLog;

require dirname(__DIR__) . '/bootstrap.php';
require $packageDir . '/scripts/develop/ini.php';

$logDir = dirname(dirname(__DIR__)) . '/data/log';

// dev tools: fatal error / syntax error / exception handler
$dev = new Dev;
$dev->loadDevFunctions()
    ->registerFatalErrorHandler()
    ->registerExceptionHandler($logDir)
    ->registerSyntaxErrorEdit();

// direct file for built in web server
if ($dev->directAccessFile() === false) {
    return false;
}


// console args
$mode = isset($argv[3]) ? $argv[3] : $mode;
$app = require dirname(__DIR__) . '/instance.php';
/** @var $app \BEAR\Package\Provide\Application\AbstractApp */

// Use cli parameter for routing (web.php get /)
if (PHP_SAPI === 'cli') {
    $app->router->setArgv($argv);
} else {
    $app->router->setGlobals($GLOBALS);
    $argv = [];
}

// development web service (/dev)
$dev->setApp($app)->webService();

// resource log
$app->logger->register($app);

return $app;
