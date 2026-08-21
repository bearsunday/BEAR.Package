<?php

declare(strict_types=1);

/**
 * Internal worker for Compiler::phar(), not a public API.
 *
 * phar.readonly is INI_SYSTEM, so writing an archive takes a fresh process.
 *
 * usage: php -d phar.readonly=0 phar-worker.php <appDir> <buildDir> <entry> <output>
 */

use BEAR\Package\Compiler\PharBuilder;

if ($argc !== 5) {
    echo 'usage: php -d phar.readonly=0 phar-worker.php <appDir> <buildDir> <entry> <output>' . PHP_EOL;
    exit(1);
}

if (ini_get('phar.readonly') !== '0') {
    fwrite(STDERR, 'phar.readonly is on: start this worker with -d phar.readonly=0.' . PHP_EOL);
    exit(1);
}

[, $appDir, $buildDir, $entry, $output] = $argv;
// A guard, not an assert(): the worker runs under the deploy's ini, where assertions are off.
$autoload = $appDir . '/vendor/autoload.php';
if ($buildDir === '' || $entry === '' || ! is_file($autoload)) {
    fwrite(STDERR, sprintf('No application to pack: "%s" holds no vendor/autoload.php, or no build directory was given.', $appDir) . PHP_EOL);
    exit(1);
}

require $autoload;

try {
    $report = (new PharBuilder())($appDir, $buildDir, $entry, $output === '' ? null : $output);
} catch (Throwable $e) {
    // The class is the diagnosis, the message the value it happened with.
    fwrite(STDERR, sprintf('%s: %s', basename(str_replace('\\', '/', $e::class)), $e->getMessage()) . PHP_EOL);
    exit(1);
}

printf('Phar: %s (%.1fMB, %d files)' . PHP_EOL, $report->path, $report->bytes / 1048576, $report->files);

if ($report->notPacked !== []) {
    printf('Not packed: %s' . PHP_EOL, implode(', ', $report->notPacked));
}

exit(0);
