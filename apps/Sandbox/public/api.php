<?php
/**
 * CLI  Built-in web server dev script
 *
 * Example:
 *
 * CLI:
 * $ php dev.php get /hello
 *
 * Built-in web server:
 *
 * $ php -S localhost:8080 dev.php
 *
 * type URL:
 *   http://localhost:8080/hello
 *   http://localhost:8080/helloresource
 *
 * @package BEAR.Framework
 * @global  $mode
 */
namespace Sandbox;

use BEAR\Sunday\Router\Router;
use BEAR\Sunday\Framework\Dispatcher;
use BEAR\Sunday\Framework\Globals;
use BEAR\Sunday\Web;
use Exception;

global $mode;

if (PHP_SAPI == 'cli-server') {
    if (preg_match('/\.(?:png|jpg|jpeg|gif|js|css|ico)$/', $_SERVER["REQUEST_URI"])) {
        return false;
    }
}

// Clear
$app = require dirname(__DIR__) . '/scripts/clear.php';

// Application
$mode = 'Api';
$app = require dirname(__DIR__) . '/scripts/instance.php';

        // Dispatch
    if (PHP_SAPI === 'cli') {
        $app->router->setArgv($argv);
        $uri = $argv[2];
        $get = [];
    } else {
        $pathInfo = isset($globals['_SERVER']['PATH_INFO']) ? $globals['_SERVER']['PATH_INFO'] : '/index';
        $uri = 'app://self' . $pathInfo;
        $get = $_GET;
    }
try {
    // Router
    list($method,) = $app->router->getMethodQuery();
    // Request
    $page = $app->resource->$method->uri($uri)->withQuery($get)->eager->request();
} catch (Exception $e) {
    $page = $app->exceptionHandler->handle($e);
}
$app->response->setResource($page)->render()->prepare()->send();
exit(0);
