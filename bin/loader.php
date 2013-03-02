#! /usr/bin/env php
<?php
/**
 * PreLoader updater
 *
 * This script is executed in each composer update to compile class-pre-loader.
 * You can manually
 *
 * $ php bin/loader.php
 *
 * @see https://github.com/mtdowling/ClassPreloader
 */
$packageDir = dirname(__DIR__);
$preLoader = $packageDir . '/vendor/classpreloader/classpreloader/classpreloader.php';
$config = __DIR__ . '/data/loader/config.php';
$output = $packageDir . '/scripts/preloader.php';
$cmd = "php {$preLoader} compile --config={$config} --output={$output}";
echo $cmd . PHP_EOL;
passthru($cmd);
