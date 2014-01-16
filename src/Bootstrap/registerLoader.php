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
 * Register auto-loader
 *
 * @param ClassLoader $loader
 * @param string      $appName
 * @param string      $appDir
 *
 * @return ClassLoader
 */
function registerLoader(ClassLoader $loader, $appName, $appDir)
{
    $loader->addPsr4($appName . '\\' , $appDir . '/src');
    AnnotationRegistry::registerLoader([$loader, 'loadClass']);
    AnnotationReader::addGlobalIgnoredName('noinspection');
    AnnotationReader::addGlobalIgnoredName('returns');

    return $loader;
}
