<?php
/**
 * Return application instance with development setup
 *
 * @global $mode
 *
 */

use BEAR\Package\Dev\Dev;

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('xhprof.output_dir', sys_get_temp_dir());

require dirname(__DIR__) . '/bootstrap.php';

// fatal error handler
Dev::registerFatalErrorHandler();

// syntax error instant edit
Dev::registerSyntaxErrorEdit();

// exception handler
$logDir = dirname(dirname(__DIR__)) . '/data/log';
Dev::registerExceptionHandler($logDir);

$dev = new Dev;

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
