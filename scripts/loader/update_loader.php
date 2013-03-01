<?php
/**
 * PreLoader updater
 *
 * usage:
 * $php update_loader.php
 *
 * @see https://github.com/mtdowling/ClassPreloader
 */
$preloader = dirname(dirname(__DIR__)) . '/vendor/classpreloader/classpreloader/classpreloader.php';
$config = dirname(dirname(__DIR__)) . '/apps/Sandbox/scripts/dev/preloader/config.php';
$output = __DIR__ . '/prod.php';
$cmd = "php {$preloader} compile --config={$config} --output={$output}";
passthru($cmd);
