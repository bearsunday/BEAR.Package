<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\Sunday\Extension\Application\AppInterface;
use Doctrine\Common\Cache\ApcCache;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Cache\VoidCache;
use Ray\Di\AbstractModule;
use Ray\Di\Injector;

final class Bootstrap
{
    /**
     * @param AbstractAppMeta $appMeta
     * @param string          $contexts
     * @param Cache           $cache
     *
     * @return AppInterface
     */
    public function newApp(AbstractAppMeta $appMeta, $contexts, Cache $cache = null)
    {
        if (is_null($cache)) {
            $cache = function_exists('apc_fetch') ? new ApcCache : new FilesystemCache($appMeta->tmpDir);
        }
        $isNotProd = strpos($contexts, 'prod') === false;
        if ($isNotProd) {
            $cache = new VoidCache;
            $this->clearDir($appMeta->tmpDir);
        }
        $appId = $appMeta->name . $contexts;
        $app = $cache->fetch($appId);
        if ($app) {
            return $app;
        }
        $app = $this->createAppInstance($appMeta, $contexts);
        $cache->save($appId, $app);

        return $app;
    }

    /**
     * @param AbstractAppMeta $appMeta
     * @param string          $contexts
     *
     * @return AppInterface
     */
    private function createAppInstance(AbstractAppMeta $appMeta, $contexts)
    {
        $tmpDir = $appMeta->tmpDir . '/' . $contexts;
        if (! file_exists($tmpDir)) {
            mkdir($tmpDir);
        }
        $appMeta->tmpDir = $tmpDir;
        $contextsArray = array_reverse(explode('-', $contexts));
        $module = new AppMetaModule($appMeta);
        foreach ($contextsArray as $context) {
            $class = $appMeta->name . '\Module\\' . ucwords($context) . 'Module';
            if (! class_exists($class)) {
                $class = 'BEAR\Package\Context\\' . ucwords($context) . 'Module';
            }
            /** @var $module AbstractModule */
            $module = new $class($module);
        }
        $app = (new Injector($module, $appMeta->tmpDir))->getInstance(AppInterface::class);

        return $app;
    }

    private function clearDir($dir)
    {
        $unlink = function ($path) use (&$unlink) {
            foreach (glob(rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*') as $file) {
                is_dir($file) ? $unlink($file) : unlink($file);
                @rmdir($file);
            }
        };
        $unlink($dir);
    }
}
