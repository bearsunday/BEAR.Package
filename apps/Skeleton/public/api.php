<?php
/**
 * CLI  Built-in web server for API
 *
 * Example:
 *
 * CLI:
 * $ php api.pgp get page://self/
 * $ php api.pgp get 'page://first/greeting?name=koriym'
 *
 * Built-in web server:
 *
 * $ php -S localhost:8089 api.php
 *
 * @package BEAR.Package
 * @global  $mode
 */
if (PHP_SAPI == 'cli-server') {
    if (preg_match('/\.(?:png|jpg|jpeg|gif|js|css|ico)$/', $_SERVER["REQUEST_URI"])) {
        return false;
    }
}
chdir(dirname(__DIR__));

// Cleaning
require 'scripts/clear.php';

$mode = 'Api';
$app = require dirname(__DIR__) . '/scripts/bootstrap/dev_instance.php';

/** @var $app \BEAR\Package\Provide\Application\AbstractApp */
    if (PHP_SAPI === 'cli') {
        $app->router->setArgv($argv);
        $uri = $argv[2];
        parse_str((isset(parse_url($uri)['query']) ? parse_url($uri)['query'] : ''), $get);
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
$app->response->setResource($page)->render()->send();
exit(0);
