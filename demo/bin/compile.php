<?php

declare(strict_types=1);

/**
 * Compile the demo, then pack it into app.phar.
 *
 * Usage:  APP_WRITE_DIR=/tmp php bin/compile.php
 *         CONTEXT picks the context (default prod-cli-hal-app), same as public/index.php.
 */

use BEAR\Package\Compiler;

require dirname(__DIR__) . '/vendor/autoload.php';

ini_set('memory_limit', '-1');

$context = getenv('CONTEXT') ?: 'prod-cli-hal-app';
$writeDir = getenv('APP_WRITE_DIR') ?: null;

$compiler = new Compiler('MyVendor\MyProject', $context, dirname(__DIR__), $writeDir);
$code = $compiler();
exit($code === 0 ? $compiler->phar() : $code);
