<?php

namespace Demo\Helloworld;

// Annotation auto loader
$loader = require  dirname(dirname(dirname(__DIR__))) . '/vendor/autoload.php';
/** @var $loader \Composer\Autoload\ClassLoader */
$loader->addPsr4('Demo\Helloworld\\', dirname(__DIR__) . '/src');
