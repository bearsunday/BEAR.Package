<?php
/**
 * This file is part of the BEAR.Package package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Package;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\AppMeta\AppMeta;
use BEAR\Resource\ResourceInterface;
use BEAR\Sunday\Extension\Application\AbstractApp;
use BEAR\Sunday\Extension\Application\AppInterface;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Cache\ApcuCache;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\ChainCache;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Cache\VoidCache;

final class Bootstrap
{
    /**
     * Return application instance
     *
     * Use newApp() instead for your own AppMeta and Cache.
     *
     * @param string $name     application name    'koriym\blog' (vendor\package)
     * @param string $contexts application context 'prd-html-app'
     * @param string $appDir   application path
     */
    public function getApp(string $name, string $contexts, string $appDir = '') : AbstractApp
    {
        return $this->newApp(new AppMeta($name, $contexts, $appDir), $contexts);
    }

    public function newApp(AbstractAppMeta $appMeta, string $contexts, Cache $cache = null) : AbstractApp
    {
        $cache = $cache ?: $this->getCache($appMeta, $contexts, $cache);
        $appId = $appMeta->name . $contexts . filemtime($appMeta->appDir . '/src');
        $app = $cache->fetch($appId); // $scriptInjector set cached single instance in wakeup
        if ($app instanceof AbstractApp) {
            return $app;
        }
        $injector = new AppInjector($appMeta->name, $contexts, $appMeta);
        $app = $injector->getInstance(AppInterface::class);
        $injector->getInstance(Reader::class);
        $injector->getInstance(Cache::class);
        $injector->getInstance(ResourceInterface::class);
        $cache->save($appId, $app);

        return $app;
    }

    private function getCache(AbstractAppMeta $appMeta, string $contexts, Cache $cache = null) : Cache
    {
        $isCacheable = \is_int(strpos($contexts, 'prod-')) || \is_int(strpos($contexts, 'stage-'));
        $cache = $cache ?: ($isCacheable ? new ChainCache([new ApcuCache, new FilesystemCache($appMeta->tmpDir)]) : new VoidCache);

        return $cache;
    }
}
