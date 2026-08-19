<?php

declare(strict_types=1);

/**
 * Internal compile script of bin/bear.compile
 *
 * @deprecated Prefer the application's own `bin/compile.php` with `new Compiler($appName, $context, $appDir)`.
 * @see https://github.com/bearsunday/BEAR.Package/issues/482
 */

use BEAR\Package\Compiler;

init:
    ini_set('memory_limit', '-1');
    $opt = getopt('n:c:d:o');
    [$appName, $context, $appDir] = [$opt['n'], $opt['c'], $opt['d']];
    require realpath($appDir) . '/vendor/autoload.php';

compile:
    $compiler = new Compiler($appName, $context, $appDir);
    if (isset($opt['o'])) {
        $code = $compiler->dumpAutoload();
        exit($code);
    }

    $report = $compiler->compile();
    echo PHP_EOL;
    printf("Compilation took %s seconds and used %sMB of memory\n", $report['time'], $report['memory']);
    printf("Compiled: %d resource classes\n", $report['compiled']);
    printf("Preload compile: %s\n", $report['preload']);
    printf("Object graph diagram: %s\n", $report['dot']);
    exit(0);
