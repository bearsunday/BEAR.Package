<?php

namespace Demo\Sandbox;

/**
 * Autoloader
 *
 * @return $app \Composer\Autoload\ClassLoader
 */
use Doctrine\Common\Annotations\AnnotationRegistry;

$packageDir = dirname(dirname(dirname(dirname(__DIR__))));
$loader = require $packageDir . '/vendor/autoload.php';
/** @var $loader \Composer\Autoload\ClassLoader */

\BEAR\Bootstrap\registerLoader(
    $loader,
    $packageDir,
    __NAMESPACE__,
    dirname(__DIR__)
);
