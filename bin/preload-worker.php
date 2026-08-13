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

// Buffer before the first include: a deprecation printed by any autoloaded file would mark
// the headers as sent, and the responder this boot must record skips its work when they are.
// Failures go to STDERR, which no buffer touches.
ob_start();
require $appDir . '/vendor/autoload.php';

try {
    (new PreloadRecorder())($appName, $context, $appDir, $writeDir === '' ? null : $writeDir);
} catch (PreloadRecordException $e) {
    fwrite(STDERR, $e->getMessage() . PHP_EOL);

    exit(1);
} finally {
    ob_end_clean();
}
