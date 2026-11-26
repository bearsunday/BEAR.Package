<?php

declare(strict_types=1);

use FakeVendor\HelloWorld\StubbedRepository;
use Ray\Compiler\CompiledInjector;

// A process production would be: the compiled scripts, the real classes, no .compile.php.
$appDir = dirname(__DIR__) . '/Fake/fake-app';
require $appDir . '/vendor/autoload.php';

$injector = new CompiledInjector($appDir . '/var/build/' . $argv[1] . '/di');
$repository = $injector->getInstance(StubbedRepository::class);
assert($repository instanceof StubbedRepository);

echo json_encode([
    'foo' => $repository->foo::class,
    'store' => $repository->store(),
    'env' => $injector->getInstance('', 'compile_env'),
], JSON_THROW_ON_ERROR);
