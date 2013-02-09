<?php
/**
 * CLI / Built-in web server script for development
 *
 * CLI:
 * $ php web.php get /
 * $ php web.php get / prod
 * $ php web.php get / api
 *
 * Built-in web server:
 * $ php -S localhost:8080 web.php
 *
 * @package BEAR.Package
 * @global  $mode string
 */

// Get application instance with $mode (Prod, Dev, Api, Stub, Test)
$mode = 'Dev';
$app = require dirname(__DIR__) . '/scripts/bootstrap/dev_instance.php';

// Cleaning (comment out to enable cache)
require  dirname(__DIR__) . '/scripts/clear.php';

// Return if direct file access in built-in web server
if (! $app) {
    return false;
}
/** @var $app \BEAR\Package\Provide\Application\AbstractApp */

// Dispatch
list($method, $pagePath, $query) = $app->router->match();

try {
    // Request
    $app->page = $app->resource->$method->uri('page://self/' . $pagePath)->withQuery($query)->eager->request();
    // Transfer
    $app->response->setResource($app->page)->render()->send();
    exit(0);
} catch(Exception $e) {
    $app->exceptionHandler->handle($e);
    exit(1);
}
