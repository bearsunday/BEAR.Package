<?php

declare(strict_types=1);

/**
 * Internal preload worker for Compiler::compile()
 *
 * Boots the application from its compiled scripts in this clean PHP process and writes
 * preload.php from the classes that boot loads. The compile process cannot measure this:
 * it has the compiler and the module tree in memory already. This script is not a public
 * API; it is spawned only by BEAR\Package\Compiler.
 *
 * usage: php preload-worker.php <appName> <context> <appDir> <writeDir>
 */

use BEAR\Package\Compiler\PreloadRecorder;
use BEAR\Package\Exception\PreloadRecordException;

if ($argc !== 5) {
    echo 'usage: preload-worker.php <appName> <context> <appDir> <writeDir>' . PHP_EOL;
    exit(1);
}

[, $appName, $context, $appDir, $writeDir] = $argv;
// The caller builds these from a resolved Meta; empty ones would compile a different application.
assert($appName !== '' && $context !== '' && $appDir !== '');

// This process writes a file; its stdout is piped into the compile report. Buffer from the
// first include so the boot's own output - deprecations, the transferred response - stays out
// of it. Failures go to STDERR, which no buffer touches.
ob_start();
require $appDir . '/vendor/autoload.php';

try {
    (new PreloadRecorder())($appName, $context, $appDir, $writeDir === '' ? null : $writeDir);
    ob_end_clean();
} catch (PreloadRecordException $e) {
    // Before exit(), which runs no finally: the shutdown flush would otherwise put a half
    // boot's output into the compile report. The message goes to STDERR, which no buffer holds.
    ob_end_clean();
    fwrite(STDERR, $e->getMessage() . PHP_EOL);

    exit(1);
}
