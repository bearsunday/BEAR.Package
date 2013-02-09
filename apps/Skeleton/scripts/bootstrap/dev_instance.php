<?php
/**
 * Application instance
 * + development initialization
 *
 * @global $mode
 */

// built-in web server
if (php_sapi_name() == "cli-server") {
    if (preg_match('/\.(?:png|jpg|jpeg|gif|js|css|ico|php)$/', $_SERVER["REQUEST_URI"])) {
        return false;
    }
    if (is_file(__DIR__.preg_replace('#(\?.*)$#', '', $_SERVER['REQUEST_URI']))) {
        return false;
    }
}

// Init
error_reporting(E_ALL);
ini_set('display_errors', 1);
//ini_set('xdebug.collect_params', '0');

$packageDir = dirname(dirname(dirname(dirname(__DIR__))));

// Load
require  $packageDir . '/scripts/debug_load.php';

// profiler
require  $packageDir . '/scripts/profile.php';

// set exception handler for development
set_exception_handler(include $packageDir . '/scripts/debugger/exception_handler.php');

// set fatal error handler
register_shutdown_function(include $packageDir . '/scripts/debugger/shutdown_error_handler.php');


// Application
$mode = isset($argv[3]) ? ucfirst($argv[3]) : $mode;
$app = require dirname(__DIR__) . '/instance.php';

// Log
$app->logger->register($app);

// Use cli parameter for routing (web.php get /)
if (PHP_SAPI === 'cli') {
    $app->router->setArgv($argv);
}

return $app;