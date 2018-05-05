<?php
/**
 * This file is part of the BEAR.Package package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
$loader = require dirname(__DIR__) . '/vendor/autoload.php';
/* @var $loader \Composer\Autoload\ClassLoader */
\Doctrine\Common\Annotations\AnnotationRegistry::registerLoader([$loader, 'loadClass']);
require __DIR__ . '/hash.php';

delete_dir(__DIR__ . '/tmp');
delete_dir(__DIR__ . '/Fake/fake-app/var/tmp');
delete_dir(__DIR__ . '/Fake/fake-app/var/log');
delete_dir(dirname(__DIR__) . '/var/tmp');
