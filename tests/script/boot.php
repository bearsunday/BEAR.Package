<?php

declare(strict_types=1);

use BEAR\Package\Injector;
use BEAR\Resource\ResourceObject;
use BEAR\Sunday\Extension\Application\AppInterface;
use FakeVendor\HelloWorld\Module\App;

require dirname(__DIR__, 2) . '/vendor/autoload.php';
//require __DIR__ . '/benchmark.php';

$opt = getopt('c:n:');
$context = isset($opt['c']) && is_string($opt['c']) ? $opt['c'] : 'prod-app';
$appDir = dirname(__DIR__) . '/Fake/fake-app';
$injector = Injector::getInstance('FakeVendor\HelloWorld', $context, $appDir);
$app = $injector->getInstance(AppInterface::class);
assert($app instanceof App);
$ro = $app->resource->get('/');

exit((int) ! ($ro instanceof ResourceObject));
