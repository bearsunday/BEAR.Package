<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Bootstrap;

use Composer\Autoload\ClassLoader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\ApcCache;
use Doctrine\Common\Cache\FilesystemCache;
use BEAR\Package\Module\Di\DiCompilerProvider;
use Doctrine\Common\Cache\Cache;

final class Bootstrap
{
    /**
     * @param ClassLoader $loader
     * @param string      $appName
     * @param string      $appDir
     */
    public static function registerLoader(ClassLoader $loader, $appName, $appDir)
    {
        /** @var $loader \Composer\Autoload\ClassLoader */
        $loader->addPsr4($appName . '\\', $appDir . '/src');

        AnnotationRegistry::registerLoader([$loader, 'loadClass']);
        AnnotationReader::addGlobalIgnoredName('noinspection');
        AnnotationReader::addGlobalIgnoredName('returns');
    }

    /**
     * @param string $appName
     * @param string $context
     * @param string $tmpDir
     * @param Cache $cache
     *
     * @return \BEAR\Sunday\Extension\Application\AppInterface
     */
    public static function getApp($appName, $context, $tmpDir, Cache $cache = null)
    {
        error_reporting(0);
        $diCompiler = (new DiCompilerProvider($appName, $context, $tmpDir, $cache))->get();
        $app = $diCompiler->getInstance('BEAR\Sunday\Extension\Application\AppInterface');
        /** $app \BEAR\Sunday\Extension\Application\AppInterface */

        return $app;
    }

    /**
     * @param array $dirs
     */
    public static function clearApp(array $dirs)
    {
        // APC Cache
        if (function_exists('apc_clear_cache')) {
            if (version_compare(phpversion('apc'), '4.0.0') < 0) {
                apc_clear_cache('user');
            }
            apc_clear_cache();
        }

        $unlink = function ($path) use (&$unlink) {
            foreach (glob(rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*') as $file) {
                is_dir($file) ? $unlink($file) : unlink($file);
                @rmdir($file);
            }
        };
        foreach ($dirs as $dir) {
            $unlink($dir);
        }
        $unlink(dirname(__DIR__) . '/var/tmp');
    }
}
