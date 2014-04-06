<?php

namespace Demo\Helloworld;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\AnnotationReader;

preload: {
//    require dirname(__DIR__) . '/var/tmp/preloader/preload.min.php';
}

autoload: {
    $loader = require  dirname(dirname(dirname(__DIR__))) . '/vendor/autoload.php';
    /** @var $loader \Composer\Autoload\ClassLoader */
    $loader->addPsr4('Demo\Helloworld\\', dirname(__DIR__) . '/src');
    AnnotationRegistry::registerLoader([$loader, 'loadClass']);
}
