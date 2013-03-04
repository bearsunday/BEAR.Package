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

if (! file_exists('../../composer.phar')) {
    $composerCmd = 'cd ../..; curl -s https://getcomposer.org/installer | php';
    passthru($composerCmd);
}
$dir = dirname(__DIR__) . '/apps/';
$cmd = "php ../../composer.phar create-project -s dev --dev bear/skeleton {$dir}/{$argv[1]}";
passthru($cmd);