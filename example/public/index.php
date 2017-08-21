<?php
use BEAR\Package\Bootstrap;
use BEAR\Sunday\Extension\Application\AbstractApp;

require dirname(__DIR__) . '/load.php';

$context = PHP_SAPI === 'cli' ? 'cli-hal-app' : 'hal-app';

$app = (new Bootstrap)->getApp('MyVendor\MyApp', $context);
/* @var $app AbstractApp */
$request = $app->router->match($GLOBALS, $_SERVER);
try {
    $page = $app
        ->resource
        ->{$request->method}
        ->uri($request->path)($request->query)
        ->transfer($app->responder, $_SERVER);
    exit(0);
} catch (\Exception $e) {
    $app->error->handle($e, $request)->transfer();
    exit(1);
}
