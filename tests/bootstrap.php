<?php

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\AnnotationReader;

error_reporting(E_ALL);

ini_set('xdebug.max_nesting_level', 300);
ini_set('display_errors', 1);

require dirname(__DIR__) . '/apps/Demo.Helloworld/bin/clear.php';
require dirname(__DIR__) . '/apps/Demo.Sandbox/bin/clear.php';

// loader
error_reporting(E_ALL ^ E_NOTICE);
$loader = require dirname(__DIR__) . '/vendor/autoload.php';
error_reporting(E_ALL);
/** @var $loader \Composer\Autoload\ClassLoader */
AnnotationRegistry::registerLoader([$loader, 'loadClass']);
AnnotationReader::addGlobalIgnoredName('noinspection');
AnnotationReader::addGlobalIgnoredName('returns');
$loader->add('BEAR\Package', [__DIR__]);
$loader->add('Demo\Sandbox', dirname(__DIR__) . '/apps/');

(new \BEAR\Package\Dev\Dev)->loadDevFunctions();
$GLOBALS['_BEAR_TEST_DIR'] = __DIR__;
$GLOBALS['_BEAR_TMP_DIR'] = __DIR__ . '/tmp';
$GLOBALS['_BEAR_PACKAGE_DIR'] = dirname(__DIR__);

$GLOBALS['_BEAR_APP'] = require dirname(__DIR__) . '/apps/Demo.Sandbox/bootstrap/instance.php';
