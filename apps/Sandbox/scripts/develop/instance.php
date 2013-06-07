<?php
/**
 * Return application instance with development setup
 *
 * @global $mode
 */

use BEAR\Package\Dev\Dev;

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

// Use cli parameter for routing (web.php get /)
if (PHP_SAPI === 'cli') {
    $app->router->setArgv($argv);
} else {
    $app->router->setGlobals($GLOBALS);
    $argv = [];
}

// /dev web service
$dev->setApp($app)->webService();

return $app;
