<?php

$loader = require dirname(__DIR__) . '/vendor/autoload.php';
/* @var $loader \Composer\Autoload\ClassLoader */
\Doctrine\Common\Annotations\AnnotationRegistry::registerLoader([$loader, 'loadClass']);

$rm = function ($dir) use (&$rm) {
    foreach (glob($dir . '/*') as $file) {
        is_dir($file) ? $rm($file) : unlink($file);
        @rmdir($file);
    }
};
$rm (__DIR__ . '/tmp');
$rm (__DIR__ . '/Fake/fake-app/var/tmp');
$rm (__DIR__ . '/Fake/fake-app/var/log');
$rm (dirname(__DIR__) . '/var/tmp');
