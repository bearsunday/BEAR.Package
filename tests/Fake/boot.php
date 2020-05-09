<?php

declare(strict_types=1);

use BEAR\Package\Bootstrap;
use BEAR\Resource\ResourceObject;
use BEAR\Sunday\Extension\Application\AppInterface;

init:
    error_reporting(E_ALL);
    ini_set('error_log', __DIR__ . '/error.log');
run:
    $packageDir = dirname(__DIR__, 2);
    require $packageDir . '/vendor/autoload.php';
    $app = (new Bootstrap)->getApp('FakeVendor\HelloWorld', 'app', $packageDir . '/tests/Fake/fake-app', '');
    $ro = $app->resource->newInstance('/');

    exit((int) ! ($app instanceof AppInterface && $ro instanceof ResourceObject));
