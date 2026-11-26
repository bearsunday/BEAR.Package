<?php

declare(strict_types=1);

/**
 * Internal DI worker for Compiler::compile()
 *
 * Writes the DI scripts in a process that never loads .compile.php: a class stub replaces the
 * real class for the whole process, and scripts written there take the stub's constructor,
 * setters and interceptor bindings. This script is not a public API; it is spawned only by
 * BEAR\Package\Compiler when the application has a .compile.php.
 *
 * usage: php di-worker.php <appName> <context> <appDir> <scriptDir> <globals>
 *
 * <globals> is the JSON of the $_SERVER and $_ENV values the compile process set, .compile.php's among them.
 */

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\AppMeta\Meta;
use BEAR\Package\Module;
use BEAR\Package\Module\ResourceObjectModule;
use Ray\Compiler\Compiler;
use Ray\Di\Injector;

if ($argc !== 6) {
    echo 'usage: di-worker.php <appName> <context> <appDir> <scriptDir> <globals>' . PHP_EOL;
    exit(1);
}

[, $appName, $context, $appDir, $scriptDir, $globals] = $argv;
assert($appName !== '' && $context !== '' && $appDir !== '' && $scriptDir !== '');
require $appDir . '/vendor/autoload.php';

/** @var array{server: array<string, scalar>, env: array<string, scalar>} $stubGlobals */
$stubGlobals = json_decode($globals, true, 512, JSON_THROW_ON_ERROR);
foreach ($stubGlobals['server'] as $key => $value) {
    $_SERVER[$key] = $value;
}

foreach ($stubGlobals['env'] as $key => $value) {
    $_ENV[$key] = $value;
}

// The same AbstractAppMeta and module tree Compiler::compile() emits from.
$meta = new Meta($appName, $context, $appDir);
$metaTree = (new Module())($meta, $context);
$metaTree->install(new ResourceObjectModule($meta->getResourceListGenerator()));
$appMeta = (new Injector($metaTree, $meta->buildDir . '/di'))->getInstance(AbstractAppMeta::class);
assert($appMeta instanceof AbstractAppMeta);
(new Compiler())->compile((new Module())($appMeta, $context), $scriptDir);
