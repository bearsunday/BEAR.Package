<?php
/**
 * CLI / Built-in web server script for development
 *
 * example:
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
 * @package BEAR.Package
 *
 * @global  $mode
 */

// Reroute
if (php_sapi_name() == "cli-server") {
    if (preg_match('/\.(?:png|jpg|jpeg|gif|js|css|ico|php)$/', $_SERVER["REQUEST_URI"])) {
        return false;
    }
    if (is_file(__DIR__.preg_replace('#(\?.*)$#', '', $_SERVER['REQUEST_URI']))) {
        return false;
    }
}

chdir(dirname(__DIR__));

// Dev
require 'scripts/bootstrap/dev.php';

// Cleaning
require 'scripts/clear.php';

// Application
$mode = isset($argv[3]) ? ucfirst($argv[3]) : 'Dev';
$app = require 'scripts/instance.php';

/** @var $app \BEAR\Package\Provide\Application\AbstractApp */


// Log
$app->logger->register($app);
file_put_contents(dirname(__DIR__) . "/data/log/di-{$mode}.log", (string)$app->injector);

// Route
if (PHP_SAPI === 'cli') {
    $app->router->setArgv($argv);
}

// Dispatch
list($method, $pagePath, $query) = $app->router->match();

restore_exception_handler();

try {
    // Request
    $app->page = $app->resource->$method->uri('page://self/' . $pagePath)->withQuery($query)->eager->request();

    // Transfer
    $app->response->setResource($app->page)->render()->outputWebConsoleLog()->send();
    exit(0);
} catch(Exception $e) {
    $app->exceptionHandler->handle($e);
    exit(1);
}
