<?php

declare(strict_types=1);

use BEAR\Package\Injector;
use BEAR\Resource\ResourceObject;
use BEAR\Sunday\Extension\Application\AbstractApp;
use BEAR\Sunday\Extension\Application\AppInterface;

require dirname(__DIR__, 2) . '/vendor/autoload.php';
//require __DIR__ . '/benchmark.php';

$opt = getopt('c:n:');
$context = isset($opt['c']) && is_string($opt['c']) ? $opt['c'] : 'prod-app';
$cn = isset($opt['n']) && is_string($opt['n']) ? $opt['n'] : '';
$appDir = dirname(__DIR__) . '/Fake/fake-app';
$injector = Injector::getInstance('FakeVendor\HelloWorld', $context, $appDir, $cn);
$app = $injector->getInstance(AppInterface::class);
assert($app instanceof AbstractApp);
$ro = $app->resource->get('/');

exit((int) ! ($ro instanceof ResourceObject));
