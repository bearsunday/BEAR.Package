<?php

/**
 * Application compiler
 *
 * This script
 *
 * + creates var/lib/preloader/preload.php which contains all class in this application.
 * + creates application object in cache.
 * + creates all resource object in cache.
 * + creates all aspect weaved resource files.
 *
 * You can use this script in console if you use file cache.
 * But if you want to use APC cache, `include` this file once in web per deploy.
 *
 * @see https://github.com/mtdowling/ClassPreloader
 */

$appDir = isset($argv[1]) ? $argv[1] : error();

$configFile = $appDir . '/var/lib//preloader/config.php';

if (! file_exists($configFile)) {
    error("invalid app-dir:{$appDir}");
}
compile($appDir);

function compile($appDir) {

    ini_set('display_errors', 1);
    ini_set('xhprof.output_dir', sys_get_temp_dir());

    $packageDir = dirname((__DIR__));
    $preLoader = $packageDir . '/vendor/bin/classpreloader.php';
    $config = $appDir . '/var/lib//preloader/config.php';
    $output = $appDir . '/var/tmp/preloader/preload.php';

    $cmd = "php {$preLoader} compile --config={$config} --output={$output}";

    echo $cmd . PHP_EOL;
    passthru($cmd);
}

/**
 * @param string $msg
 */
function error($msg = 'Usage: php bin/compiler.php <app-dir>')
{
    error_log($msg);
    exit(1);
}
