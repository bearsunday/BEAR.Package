<?php

declare(strict_types=1);

use BEAR\Package\Compiler;

$appDir = dirname(__DIR__) . '/Fake/fake-app';
require $appDir . '/vendor/autoload.php';

// This runs via passthru(), so keep the app's own diagnostics out of the suite output.
ini_set('error_log', dirname(__DIR__) . '/tmp/error_log.txt');

// The constructor loads .compile.php itself; requiring it here would redeclare the stubs.
$compiler = new Compiler('FakeVendor\HelloWorld', 'prod-app', $appDir);
$compiler->compile();

exit($compiler->dumpAutoload());
