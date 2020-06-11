<?php

declare(strict_types=1);

use BEAR\Package\Compiler;

init:
    $opt = getopt('n:c:d:o');
    [$appName, $context, $appDir] = [$opt['n'], $opt['c'], $opt['d']];
    require realpath($appDir) . '/vendor/autoload.php';

compile:
    $compiler = new Compiler($appName, $context, $appDir);
    $code = isset($opt['o']) ? $compiler->dumpAutoload() : $compiler->compile();
    exit($code);
