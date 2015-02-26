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
use Ray\Di\Injector;

final class Bootstrap
{
    const PACKAGE_MODULE_PATH = 'BEAR\Package\Context\\';

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
            $cache = $this->contextCache($appMeta);
        }
        $app = $cache->fetch($contexts);
        if ($app) {
            return $app;
        }
        $contextsArray = array_reverse(explode('-', $contexts));
        $module = null;
        foreach ($contextsArray as $context) {
            $class = $appMeta->name . '\Module\\' . ucwords($context) . 'Module';
            if (! class_exists($class)) {
                $class = self::PACKAGE_MODULE_PATH . ucwords($context) . 'Module';
            }
            $module =  new $class($module);
        }
        $app = (new Injector($module, $appMeta->tmpDir))->getInstance(AppInterface::class);
        $cache->save($contexts, $app);

        return $app;
    }

    public function contextCache(AbstractAppMeta $appMeta)
    {
        $cache = function_exists('apc_fetch') ? new ApcCache : new FilesystemCache($appMeta->tmpDir);
        $cache->setNamespace(filemtime($appMeta->appDir . '/src/.'));

        return $cache;
    }
}
