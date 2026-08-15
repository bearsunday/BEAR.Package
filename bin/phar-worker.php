<?php

declare(strict_types=1);

/**
 * Internal phar worker for Compiler::phar()
 *
 * phar.readonly is INI_SYSTEM - no running process can turn it off - so the Compiler
 * spawns this script with -d phar.readonly=0. This script is not a public API.
 *
 * usage: php -d phar.readonly=0 phar-worker.php <context> <appDir> <entry> <output>
 */

use BEAR\Package\Compiler\PharBuilder;

if ($argc !== 5) {
    echo 'usage: php -d phar.readonly=0 phar-worker.php <context> <appDir> <entry> <output>' . PHP_EOL;
    exit(1);
}

[, $context, $appDir, $entry, $output] = $argv;
assert($context !== '' && $appDir !== '' && $entry !== '');
require $appDir . '/vendor/autoload.php';

try {
    $report = (new PharBuilder())($context, $appDir, $entry, $output === '' ? null : $output);
} catch (Throwable $e) {
    fwrite(STDERR, $e->getMessage() . PHP_EOL);
    exit(1);
}

printf('Phar: %s (%.1fMB, %d files)' . PHP_EOL, $report->path, $report->bytes / 1048576, $report->files);
if ($report->writeDir !== null) {
    printf('Boot: APP_WRITE_DIR=%s php %s' . PHP_EOL, $report->writeDir, $report->path);
}

exit(0);
