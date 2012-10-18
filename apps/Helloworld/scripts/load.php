<?php
namespace Helloworld;

use Doctrine\Common\Annotations\AnnotationRegistry;

$packageDir = dirname(dirname(dirname(__DIR__)));

// Auto loader
require_once $packageDir . '/vendor/autoload.php';

// Core file loader
require_once $packageDir . '/vendor/bear/sunday/scripts/core_loader.php';

// Annotation auto loader
AnnotationRegistry::registerAutoloadNamespace(__NAMESPACE__ . '\Annotation\\', dirname(__DIR__));
AnnotationRegistry::registerAutoloadNamespace('Ray\Di\Di\\', $packageDir . '/vendor/ray/di/src/');
AnnotationRegistry::registerAutoloadNamespace('BEAR\Resource\Annotation\\', $packageDir . '/vendor/bear/resource/src/');
AnnotationRegistry::registerAutoloadNamespace('BEAR\Sunday\Annotation\\', $packageDir . '/vendor/bear/sunday/src/');
