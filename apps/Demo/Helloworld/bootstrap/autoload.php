<?php

namespace Demo\Helloworld;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\AnnotationReader;

$packageDir = dirname(dirname(dirname(dirname(__DIR__))));
// Annotation auto loader
$loader = require $packageDir . '/vendor/autoload.php';
/** @var $loader \Composer\Autoload\ClassLoader */
$loader->set('Demo\Helloworld', dirname(__DIR__) . '/src');
AnnotationRegistry::registerLoader([$loader, 'loadClass']);
AnnotationReader::addGlobalIgnoredName('noinspection');
