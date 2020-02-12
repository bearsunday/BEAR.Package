<?php

declare(strict_types=1);

use BEAR\Package\Bootstrap;

require dirname(__DIR__) . '/vendor/autoload.php';

$context = PHP_SAPI === 'cli' ? 'cli-hal-app' : 'hal-app';

$app = (new Bootstrap)->getApp('MyVendor\MyProject', $context);
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
