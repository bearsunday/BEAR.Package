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
use Ray\Aop\Matcher;
use Ray\Di\AbstractModule;
use Ray\Di\Injector;
use Ray\Di\CacheableModule;
use Doctrine\Common\Cache\Cache;
use Ray\Di\CacheInjector;

class Bootstrap
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
     * Return compiled application instance
     *
     * (experimental)
     *
     * @param $appName
     * @param $context
     * @param $tmpDir
     *
     * @return \BEAR\Sunday\Extension\Application\AppInterface
     */
    public static function getCompiledApp($appName, $context, $tmpDir, Cache $cache = null)
    {
        $diCompiler = (new DiCompilerProvider($appName, $context, $tmpDir, $cache))->get();
        $app = $diCompiler->getInstance('BEAR\Sunday\Extension\Application\AppInterface');
        /** $app \BEAR\Sunday\Extension\Application\AppInterface */

        return $app;
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
        $appModule = $appName . '\Module\AppModule';
        $cache = $cache ?: function_exists('apc_fetch') ? new ApcCache : new FilesystemCache($tmpDir);
        $cacheKey = 'module-' . $appName . $context;
        $module = $cache->fetch($cacheKey);
        if (! $module) {
            $module = new $appModule($context);
            $cache->save($cacheKey, $module);
        }

        $moduleProvider = function () use ($appModule, $context) {return new $appModule($context);};
        $module = new CacheableModule($moduleProvider, $cacheKey);
//        $injector = Injector::create([new $appModule($context)], $cache, $tmpDir)->enableBindCache();
        $injector = Injector::create([$module], $cache, $tmpDir)->enableBindCache();
        $app = $injector->getInstance('BEAR\Sunday\Extension\Application\AppInterface');

        /** $app \BEAR\Sunday\Extension\Application\AppInterface */
        return $app;
    }

    /**
     * Return cached application instance
     *
     * (experimental)
     *
     * @param string $appName
     * @param string $context
     * @param string $tmpDir
     * @param Cache $cache
     *
     * @return \BEAR\Sunday\Extension\Application\AppInterface
     */
    public static function getCachedApp($appName, $context, $tmpDir, Cache $cache = null)
    {
        $appModule = $appName . '\Module\AppModule';
        $cache = $cache ?: function_exists('apc_fetch') ? new ApcCache : new FilesystemCache($tmpDir);
        $cacheKey = $appName . $context;
        $initialization = function () {
            // initialize per system startup (not per each request)
        };
        $injector = function () use ($appModule, $cache) {
            return Injector::create([new $appModule], $cache,  __DIR__ . '/tmp');
        };
        $injector = new CacheInjector($injector, $initialization, $cacheKey, $cache);
        $app = $injector->getInstance('BEAR\Sunday\Extension\Application\AppInterface');

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
