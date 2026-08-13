<?php

declare(strict_types=1);

use BEAR\Package\Compiler;
use BEAR\Package\Injector;

$appDir = dirname(__DIR__) . '/Fake/fake-app';
require $appDir . '/vendor/autoload.php';

// This runs via passthru(), so keep the app's own diagnostics out of the suite output.
ini_set('error_log', dirname(__DIR__) . '/tmp/error_log.txt');

$factory = $argv[1] ?? '';
if ($factory === 'constructor') {
    $compiler = new Compiler('FakeVendor\HelloWorld', 'prod-app', $appDir);
    $compiler->compile();
    exit($compiler->dumpAutoload());
}

if ($factory !== 'from-injector') {
    exit(1);
}

require $appDir . '/.compile.php';
// The skeleton uses its app-specific Injector; this fixture reproduces getInstance before Compiler tracking.
$injector = Injector::getInstance('FakeVendor\HelloWorld', 'prod-app', $appDir);
exit(Compiler::fromInjector($injector, 'prod-app')());
