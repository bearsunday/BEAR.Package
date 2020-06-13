<?php

declare(strict_types=1);

use BEAR\Package\Injector;
use BEAR\Resource\ResourceObject;
use BEAR\Sunday\Extension\Application\AppInterface;

init:
    require dirname(__DIR__, 2) . '/vendor/autoload.php';
    error_reporting(E_ALL);
run:
    $packageDir = dirname(__DIR__, 2);
    $injector = Injector::getInstance('FakeVendor\HelloWorld', 'hal-app', $packageDir . '/tests/Fake/fake-app', '');
    $app = $injector->getInstance(AppInterface::class);
    $ro = $app->resource->newInstance('/');
    exit((int) ! ($app instanceof AppInterface && $ro instanceof ResourceObject));
