#!/usr/bin/env php
<?php

declare(strict_types=1);

use BEAR\Package\Compiler;

[,$appName, $context, $appDir] = $argv;
require realpath($appDir) . '/vendor/autoload.php';

try {
    error_log((new Compiler)($appName, $context, $appDir));
    exit(0);
} catch (\Exception $e) {
    error_log((string) $e);
    exit(1);
}
