<?php
/**
 * This file is part of the BEAR.Framework package
 *
 * @package BEAR.Framework
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\AnnotationReader;

error_reporting(E_ALL);

ob_start(); // to hide checker message
$isInstallOk = require dirname(__DIR__) . '/bin/env.php';
if (!$isInstallOk) {
    echo "Please fix the install problem before tests." . PHP_EOL;
    exit(1);
}
ob_end_clean();

// loader

require dirname(__DIR__) . '/vendor/autoload.php';
AnnotationRegistry::registerAutoloadNamespace('Ray\Di\Di\\', dirname(__DIR__) . '/vendor/ray/di/src');
AnnotationRegistry::registerAutoloadNamespace(
    'BEAR\Resource\Annotation\\',
    dirname(__DIR__) . '/vendor/bear/resource/src/'
);
AnnotationRegistry::registerAutoloadNamespace('BEAR\Sunday\Annotation\\', dirname(__DIR__) . '/src/');
AnnotationReader::addGlobalIgnoredName('noinspection');
AnnotationReader::addGlobalIgnoredName('returns');

define('_BEAR_TEST_DIR', __DIR__);
ini_set('error_log', sys_get_temp_dir() . '/error.log');

error_log("test start...");
