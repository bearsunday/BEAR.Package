<?php
/**
 * Application loader script
 *
 *  set composer auto loader
 *  set silent auto loader for doctrine annotation
 *  set ignore annotation
 */
namespace Sandbox;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\AnnotationReader;

$packageDir = dirname(dirname(dirname(__DIR__)));

// Auto loader
require_once $packageDir . '/vendor/autoload.php';

// Core file loader
//require_once $packageDir . '/vendor/bear/sunday/scripts/core_loader.php';

// Annotation auto loader
AnnotationRegistry::registerAutoloadNamespace(__NAMESPACE__ . '\Annotation\\', dirname(dirname(__DIR__)));
AnnotationRegistry::registerAutoloadNamespace('Ray\Di\Di\\', $packageDir . '/vendor/ray/di/src/');
AnnotationRegistry::registerAutoloadNamespace('BEAR\Resource\Annotation\\', $packageDir . '/vendor/bear/resource/src/');
AnnotationRegistry::registerAutoloadNamespace('BEAR\Sunday\Annotation\\', $packageDir . '/vendor/bear/sunday/src/');
AnnotationReader::addGlobalIgnoredName('noinspection');
AnnotationReader::addGlobalIgnoredName('returns'); // for Mr.Smarty. :(
unset($packageDir);
