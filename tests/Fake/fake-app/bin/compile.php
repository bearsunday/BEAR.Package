#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Compile entry of the fake application
 *
 * An application owns its compile entry and hands its own injector to the
 * compiler, so the compile honours the same Meta path policy as runtime.
 *
 * Usage: php tests/Fake/fake-app/bin/compile.php [context]
 *
 * A real application requires `dirname(__DIR__) . '/vendor/autoload.php'`;
 * this fixture shares the vendor directory of BEAR.Package itself.
 */

use BEAR\Package\Compiler;
use BEAR\Package\Injector;

require dirname(__DIR__, 4) . '/vendor/autoload.php';

$context = $argv[1] ?? 'prod-app';
$injector = Injector::getInstance('FakeVendor\HelloWorld', $context, dirname(__DIR__));

exit(Compiler::fromInjector($injector, $context)());
