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
if (! isset($argv[1])) {
    throw new \LogicException('new application name required. usage: new_app.php MyApp');
}

$composerPath = dirname(__DIR__) . '/composer.phar';
if (! file_exists($composerPath)) {
    $composerCmd = 'curl -s https://getcomposer.org/installer | php';
    passthru($composerCmd);
    $composerPath = dirname(__DIR__) . '/composer.phar';
}
$dir = dirname(__DIR__) . '/apps/';
$cmd = "php {$composerPath} create-project -s dev --dev bear/skeleton {$dir}/{$argv[1]}";
passthru($cmd);
