<?php

declare(strict_types=1);

use BEAR\AppMeta\Meta;
use BEAR\Package\Injector\PackageInjector;

require dirname(__DIR__, 2) . '/vendor/autoload.php';

// An entry point that declares anonymous classes before it builds the same override module
for ($i = 0; $i < (int) $argv[1]; $i++) {
    eval('new class {};');
}

$module = require __DIR__ . '/override-module.php';
$meta = new Meta('FakeVendor\HelloWorld', 'app', dirname(__DIR__) . '/Fake/fake-app');
$scriptDir = new ReflectionMethod(PackageInjector::class, 'scriptDir');
echo json_encode([
    'class' => $module::class,
    'devDir' => $scriptDir->invoke(null, $meta, $module, false),
    'prodDir' => $scriptDir->invoke(null, $meta, $module, true),
]);
