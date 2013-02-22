<?php
/**
 * CLI Built-in web server for API
 *
 * This is an entry point for an API response based application build.
 *
 * Examples:
 *
 * CLI:
 * $ php api.pgp get page://self/
 * $ php api.pgp get 'page://first/greeting?name=koriym'
 *
 * Built-in web server:
 *
 * $ php -S localhost:8089 api.php
 *
 * @package Skeleton
 * @global  $mode
 */
if (PHP_SAPI == 'cli-server') {
    if (preg_match('/\.(?:png|jpg|jpeg|gif|js|css|ico)$/', $_SERVER["REQUEST_URI"])) {
        return false;
    }
}
chdir(dirname(__DIR__));

/**
 * The cache is cleared on each request via the following script. We understand that you may want to debug
 * your application with caching turned on. When doing so just comment out the following.
 */
require 'scripts/clear.php';

/**
 * Here we get an application instance by setting a $mode variable such as (Prod, Dev, Api, Stub, Test)
 * the dev instance provides debugging tools and defaults to help you the development of your application.
 */
$mode = 'Api';
$app = require dirname(__DIR__) . '/scripts/bootstrap/dev_instance.php';

/**
 * When using the CLI we set the router arguments needed for CLI use.
 * Otherwise just get the path directly from the globals.
 *
 * @var $app \BEAR\Package\Provide\Application\AbstractApp
 */
if (PHP_SAPI === 'cli') {
    $app->router->setArgv($argv);
    $uri = $argv[2];
    parse_str((isset(parse_url($uri)['query']) ? parse_url($uri)['query'] : ''), $get);
} else {
    $pathInfo = $_SERVER['PATH_INFO'] ? $_SERVER['PATH_INFO'] : '/index';
    $uri = 'app://self' . $pathInfo;
    $get = $_GET;
}

/**
 * Get the method from the router and attempt to request the resource and render.
 * On failure trigger the error handler.
 */
try {
    list($method,) = $app->router->match();
    $page = $app->resource->$method->uri($uri)->withQuery($get)->eager->request();
} catch (Exception $e) {
    $page = $app->exceptionHandler->handle($e);
}
$app->response->setResource($page)->render()->send();
exit(0);
