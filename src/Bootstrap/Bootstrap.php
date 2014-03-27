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
use BEAR\Package\Module\Di\DiCompilerProvider;

class Bootstrap
{
    /**
     * @param string $appName
     * @param string $appDir
     *
     * @return void
     */
    public static function registerLoader(ClassLoader $loader, $appName, $appDir)
    {
        /** @var $loader \Composer\Autoload\ClassLoader */
        $loader->addPsr4($appName . '\\' , $appDir . '/src');

        AnnotationRegistry::registerLoader([$loader, 'loadClass']);
        AnnotationReader::addGlobalIgnoredName('noinspection');
        AnnotationReader::addGlobalIgnoredName('returns');
    }

    /**
     * @param $appName
     * @param $context
     * @param $tmpDir
     *
     * @return \BEAR\Sunday\Extension\Application\AppInterface
     */
    public static function getApp($appName, $context, $tmpDir)
    {
        $diCompiler = (new DiCompilerProvider($appName, $context, $tmpDir))->get();
        $app = $diCompiler->getInstance('BEAR\Sunday\Extension\Application\AppInterface');
        /** $app \BEAR\Sunday\Extension\Application\AppInterface */

        return $app;
    }

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
