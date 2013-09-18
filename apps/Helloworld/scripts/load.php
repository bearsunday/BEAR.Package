<?php

namespace Helloworld;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\AnnotationReader;

$packageDir = dirname(dirname(dirname(__DIR__)));
ini_set('xdebug.max_nesting_level', 200);
// Annotation auto loader
$loader = require $packageDir . '/vendor/autoload.php';
/** @var $loader \Composer\Autoload\ClassLoader */
$loader->set('Helloworld', dirname(dirname(__DIR__)));
AnnotationRegistry::registerLoader([$loader, 'loadClass']);
AnnotationReader::addGlobalIgnoredName('noinspection');
unset($packageDir);
