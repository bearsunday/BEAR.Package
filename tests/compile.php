<?php

declare(strict_types=1);

use BEAR\Package\Compiler;

init:
    ini_set('memory_limit', '-1');
    $opt = getopt('n:c:d:o');
//    [$appName, $context, $appDir] = [$opt['n'], $opt['c'], $opt['d']];
    $appName = 'FakeVendor\HelloWorld';
    $context = 'prod-app';
    $appDir = dirname(__DIR__) . '/tests/Fake/fake-app';
    require realpath($appDir) . '/vendor/autoload.php';

compile:
    $compiler = new Compiler($appName, $context, $appDir);
    $code = isset($opt['o']) ? $compiler->dumpAutoload() : $compiler->compile();
    exit($code);
