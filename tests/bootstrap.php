<?php

// loader
$loader = require dirname(__DIR__) . '/vendor/autoload.php';
/* @var $loader \Composer\Autoload\ClassLoader */
$loader->addPsr4('BEAR\Package\\', __DIR__);
$loader->addPsr4('BEAR\Package\\', __DIR__ . '/Fake');
$loader->addPsr4('FakeVendor\HelloWorld\\', __DIR__ . '/Fake/fake-app/src');
\Doctrine\Common\Annotations\AnnotationRegistry::registerLoader([$loader, 'loadClass']);

$_ENV['TEST_DIR'] = __DIR__;
$_ENV['TMP_DIR'] = __DIR__ . '/tmp';
$_ENV['PACKAGE_DIR'] = dirname(__DIR__);
