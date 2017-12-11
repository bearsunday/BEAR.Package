<?php
use BEAR\Package\Bootstrap;
use BEAR\Resource\ResourceObject;

apcu_add('i', 0);
apcu_store('i', apcu_fetch('i') + 1);

t('start');

require dirname(__DIR__) . '/autoload.php';
t('load');

/* @global string $context */
$app = (new Bootstrap)->getApp('MyVendor\MyProject', $context, dirname(__DIR__));

t('app');

$request = $app->router->match($GLOBALS, $_SERVER);

t('route');

try {
    $page = $app->resource->{$request->method}->uri($request->path)($request->query);

    t('request');

    /* @var $page ResourceObject */
    $page->transfer($app->responder, $_SERVER);

    t('transfer');

//    echo (microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000;
    exit(0);
} catch (\Exception $e) {
    $app->error->handle($e, $request)->transfer();
    exit(1);
}

function t(string $key)
{
    apcu_add($key, 0);
    $time = apcu_fetch($key) + microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
    apcu_store($key, $time);
    error_log(number_format($time / apcu_fetch('i') * 1000, 2 ) . ' '. $key. PHP_EOL);
}