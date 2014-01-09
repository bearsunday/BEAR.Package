<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */

namespace BEAR\Bootstrap;

use Composer\Autoload\ClassLoader;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;

/**
 * @param $packageDir
 * @param $appDir
 *
 * @return mixed
 */
function registerLoader(ClassLoader $loader, $appName, $appDir, $packageDir)
{
    $loader->addPsr4($appName . '\\' , $appDir . '/src');
    if (file_exists($appDir . '/vendor/composer/autoload_classmap.php')) {
        $loader->addClassMap(require $appDir . '/vendor/composer/autoload_classmap.php');
        $nameSpaces = require $appDir . '/vendor/composer/autoload_namespaces.php';
        foreach ($nameSpaces as $prefix => $path) {
            $loader->add($prefix, $path);
        }
    }
    AnnotationRegistry::registerAutoloadNamespaces(
        [
            "Ray\Di\Di\\" => $packageDir . '/vendor/ray/di/src',
            'BEAR\Sunday\Annotation' => $packageDir . '/vendor/bear/sunday/src',
            'BEAR\Package\Annotation' => $packageDir . '/vendor/bear/sunday/src',
            $appName => $appDir . '/src'
        ]
    );
    AnnotationReader::addGlobalIgnoredName('noinspection');
    AnnotationReader::addGlobalIgnoredName('returns');
    return $loader;
}
