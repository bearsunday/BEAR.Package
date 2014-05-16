<?php

ini_set('xdebug.max_nesting_level', 300);
ini_set('display_errors', 1);

require dirname(__DIR__) . '/apps/Demo.Helloworld/bin/clear.php';
require dirname(__DIR__) . '/apps/Demo.Sandbox/bin/clear.php';

// loader
error_reporting(E_ALL ^ E_NOTICE);
$loader = require dirname(__DIR__) . '/vendor/autoload.php';
/** @var $loader \Composer\Autoload\ClassLoader */
$loader->addPsr4('BEAR\Package\\', __DIR__);
$loader->addPsr4('Vendor\MockApp\\', __DIR__ . '/Mock');
error_reporting(E_ALL);

$_ENV['TEST_DIR'] = __DIR__;
$_ENV['TMP_DIR'] = __DIR__ . '/tmp';
$_ENV['PACKAGE_DIR'] = dirname(__DIR__);
