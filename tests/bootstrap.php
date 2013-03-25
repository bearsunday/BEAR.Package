<?php

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\AnnotationReader;

error_reporting(E_ALL);

// loader
$loader = require dirname(__DIR__) . '/vendor/autoload.php';
AnnotationRegistry::registerLoader([$loader, 'loadClass']);
AnnotationReader::addGlobalIgnoredName('noinspection');
AnnotationReader::addGlobalIgnoredName('returns');

define('_BEAR_TEST_DIR', __DIR__);
$GLOBALS['_BEAR_PACKAGE_DIR'] = dirname(__DIR__);

ini_set('error_log', sys_get_temp_dir() . '/error.log');
