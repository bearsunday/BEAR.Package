<?php

declare(strict_types=1);

use BEAR\Package\Injector;
use BEAR\Resource\ResourceObject;
use BEAR\Sunday\Extension\Application\AppInterface;

require dirname(__DIR__, 2) . '/vendor/autoload.php';
//require __DIR__ . '/benchmark.php';

$opt = getopt('c:n:');
$context = $opt['c'] ?? 'prod-app';
$cn = $opt['n'] ?? '';
$appDir = dirname(__DIR__) . '/Fake/fake-app';
$injector = new Injector('FakeVendor\HelloWorld', $context, $appDir, $cn);
$app = $injector->getInstance(AppInterface::class);
$ro = $app->resource->get('/');

exit((int) ! ($app instanceof AppInterface && $ro instanceof ResourceObject));
