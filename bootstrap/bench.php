<?php
use BEAR\Package\Bootstrap;
use BEAR\Resource\ResourceObject;

$context = 'prod-app';

apcu_add('i', 0);
apcu_store('i', apcu_fetch('i') + 1);

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
    if (isset($_GET['result'])) {
        result();
    }

    exit(0);
} catch (\Exception $e) {
    $app->error->handle($e, $request)->transfer();
    exit(1);
}

function t(string $key)
{
    static $i;

    apcu_add($key, 0);
    $i = $i ?: apcu_fetch('i');
    if ($i === 1) {
        return; // first call to create cache
    }
    $time = apcu_fetch($key) + microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
    apcu_store($key, $time);
}

function result()
{
    $i = apcu_fetch('i');
    $averageRequestTime = apcu_fetch('transfer') / $i;
    $lastTimer = 0;
    foreach (['load', 'app', 'route', 'request', 'transfer'] as $action) {
        $actionTime = apcu_fetch($action);
        $elapsedTime = number_format($actionTime / $i * 1000, 3);
        $periodTime = number_format(($actionTime - $lastTimer) / $i * 1000, 3);
        $lastTimer = $actionTime;
        $proportion = number_format($periodTime / $averageRequestTime / 1000 * 100, 1);
        printf("| %s | %s | %s | %s%% |\n", $action, $elapsedTime, $periodTime, $proportion);
    }
}
