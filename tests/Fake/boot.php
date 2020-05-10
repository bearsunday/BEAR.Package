<?php

declare(strict_types=1);

use BEAR\Package\Bootstrap;
use BEAR\Resource\ResourceObject;
use BEAR\Sunday\Extension\Application\AppInterface;

init:
    error_reporting(E_ALL);
run:
    $packageDir = dirname(__DIR__, 2);
    require $packageDir . '/vendor/autoload.php';
    $app = (new Bootstrap)->getApp('FakeVendor\HelloWorld', 'hal-app', $packageDir . '/tests/Fake/fake-app', '');
    $ro = $app->resource->newInstance('/');
    exit((int) ! ($app instanceof AppInterface && $ro instanceof ResourceObject));
