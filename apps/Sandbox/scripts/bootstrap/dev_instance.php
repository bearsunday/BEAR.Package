<?php
/**
 * Application instance
 * + development initialization
 *
 * @global $mode
 */

use BEAR\Package\Dev\DevWeb\DevWeb;

// Init
error_reporting(E_ALL);
ini_set('display_errors', 1);
//ini_set('xdebug.collect_params', '0');

// built-in web server
$isDevTool = PHP_SAPI  !== 'cli' && substr($_SERVER["REQUEST_URI"], 0, 5) === '/dev/';
if (! $isDevTool && php_sapi_name() == "cli-server") {
    $path = parse_url($_SERVER['REQUEST_URI'])['path'];
    if (preg_match('/\.(?:png|jpg|jpeg|gif|js|css|ico|php|html)$/', $path)) {
        return false;
    }
    if (is_file(__DIR__.preg_replace('#(\?.*)$#', '', $_SERVER['REQUEST_URI']))) {
        return false;
    }
}
$packageDir = dirname(dirname(dirname(dirname(__DIR__))));

// Loader
require  dirname(__DIR__) . '/bootstrap.php';

// debug loader
require  $packageDir . '/scripts/dev/load.php';

// profiler
//require  $packageDir . '/scripts/dev/profile.php';

// set exception handler for development
set_exception_handler(include $packageDir . '/scripts/debugger/exception_handler.php');

// set fatal error handler
register_shutdown_function(include $packageDir . '/scripts/debugger/shutdown_error_handler.php');

// debug web service (/dev)
if ($isDevTool) {
    $isAjaxReq = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    $app = $isAjaxReq ? null : (require dirname(__DIR__) . '/instance.php');
    (new DevWeb)->service($_SERVER['REQUEST_URI'], $app);
    exit(0);
}

// Application
$mode = isset($argv[3]) ? ucfirst($argv[3]) : (isset($mode) ? $mode : 'Prod');
$app = require dirname(__DIR__) . '/instance.php';
/** @var $app \BEAR\Package\Provide\Application\AbstractApp */

// Log
$app->logger->register($app);

// Use cli parameter for routing (web.php get /)
if (PHP_SAPI === 'cli' && isset($argv)) {
    $app->router->setArgv($argv);
} else {
    $app->router->setGlobals($GLOBALS);
}

return $app;
