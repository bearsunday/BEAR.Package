<?php
/**
 * CLI / Built-in web server script for development
 *
 * examaple:
 *
 * CLI:
 * $ php web.php get /
 *
 * Built-in web server:
 *
 * $ php -S localhost:8080 web.php
 *
 * type URL:
 *   http://localhost:8080/
 *
 * @global  $mode
 * @package BEAR.Sandbox
 */
namespace Sandbox;

use BEAR\Sunday\Router\Router;
use BEAR\Sunday\Framework\Globals;
use Exception;

// Reroute
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
ob_start();

// Load
require_once dirname(dirname(dirname(__DIR__))) . '/scripts/debug_load.php';

// Clear
//$app = require dirname(__DIR__) . '/scripts/clear.php';

// Application
$mode = 'Dev';
$app = require dirname(__DIR__) . '/scripts/instance.php';

// Log
$app->logger->register($app);

// Route
$globals = (PHP_SAPI === 'cli') ? $app->globals->get($argv) : $GLOBALS;
// or use router
// $router = require dirname(__DIR__) . '/scripts/router/standard_router.php';
// Dispatch
list($method, $pagePath, $query) = $app->router->match($globals);

// Request
try {
    $page = $app->resource->$method->uri('page://self/' . $pagePath)->withQuery($query)->eager->request();
} catch (Exception $e) {
    $page = $app->exceptionHandler->handle($e);
}

// Transfer
$app->response->setResource($page)->render()->prepare()->outputWebConsoleLog()->send();
exit(0);
