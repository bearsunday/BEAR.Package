<?php

declare(strict_types=1);

use BEAR\Package\Compiler;

$opt = getopt('n:c:d:o');
[$appName, $context, $appDir] = [$opt['n'], $opt['c'], $opt['d']];
require realpath($appDir) . '/vendor/autoload.php';
// $compileScript load a null object class to complete the compilation.
$compileScript = realpath($appDir) . '/.compile.php';
if (file_exists($compileScript)) {
    require $compileScript;
}

try {
    $output = (new Compiler)($appName, $context, $appDir);
    echo isset($opt['o']) ? $output . PHP_EOL : PHP_EOL;
    exit(0);
} catch (\Exception $e) {
    error_log((string) $e);
    exit(1);
}
