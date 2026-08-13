<?php

declare(strict_types=1);

/**
 * Internal compile worker for Compiler::fromInjector()
 *
 * Runs the constructor compile path in this clean PHP process so the class-tracking
 * autoloader is installed before any application class loads (#482). This script is
 * not a public API; it is spawned only by BEAR\Package\Compiler.
 *
 * usage: php compile-worker.php <appName> <context> <appDir> <writeDir>
 */

use BEAR\Package\Compiler;

if ($argc !== 5) {
    echo 'usage: compile-worker.php <appName> <context> <appDir> <writeDir>' . PHP_EOL;
    exit(1);
}

[, $appName, $context, $appDir, $writeDir] = $argv;
require $appDir . '/vendor/autoload.php';

exit((new Compiler($appName, $context, $appDir, $writeDir === '' ? null : $writeDir))());
