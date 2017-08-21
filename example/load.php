<?php
/**
 * This file is part of the BEAR.Package package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
use Doctrine\Common\Annotations\AnnotationRegistry;

$loader = (require dirname(__DIR__) . '/vendor/autoload.php');
/* @var $loader \Composer\Autoload\ClassLoader */
$loader->addPsr4('MyVendor\MyApp' . '\\', __DIR__ . '/src');
AnnotationRegistry::registerLoader('class_exists');
