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

$unlink = function ($path) use (&$unlink) {
    foreach (glob(rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*') as $file) {
        is_dir($file) ? $unlink($file) : unlink($file);
        @rmdir($file);
    }
};

$unlink($_ENV['TMP_DIR']);
$unlink(__DIR__ . '/Fake/fake-app/var/tmp');

register_shutdown_function(function () use ($unlink) {
    $unlink($_ENV['TMP_DIR']);
    $unlink(__DIR__ . '/Fake/fake-app/var/tmp');
    $unlink(__DIR__ . '/Fake/fake-app/var/log');
});
