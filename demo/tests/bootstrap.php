<?php
/* @var \Composer\Autoload\ClassLoader $loader */
$loader = require dirname(__DIR__, 2) . '/vendor/autoload.php';
$loader->addPsr4('MyVendor\\MyProject\\', dirname(__DIR__) . '/src');
