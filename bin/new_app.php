#! /usr/bin/env php
<?php
/**
 * New application
 *
 * usage:
 *
 * $ php bin/new_app.php MyApp
 *
 * test:
 *
 * cd apps/MyApp;
 * phpunit
 *
 * run:
 *
 * cd public;
 * php web.php get /
 * php -S localhost:8088 web.php
 *
 */
$appName = isset($argv[1]) ? ucwords($argv[1]) : 'NewApp';

$composerPath = dirname(__DIR__) . '/composer.phar';
if (! file_exists($composerPath)) {
    $composerCmd = 'curl -s https://getcomposer.org/installer | php';
    passthru($composerCmd);
    $composerPath = dirname(__DIR__) . '/composer.phar';
}
$dir = dirname(__DIR__) . '/apps';
$cmd = "php {$composerPath} create-project -s dev --dev bear/skeleton {$dir}/$appName";
passthru($cmd);
