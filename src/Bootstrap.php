<?php

declare(strict_types=1);

namespace BEAR\Package;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\AppMeta\Meta;
use BEAR\Sunday\Extension\Application\AbstractApp;
use BEAR\Sunday\Extension\Application\AppInterface;
use Doctrine\Common\Cache\Cache;

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
        return $this->newApp(new Meta($name, $contexts, $appDir), $contexts);
    }

    public function newApp(AbstractAppMeta $appMeta, string $contexts, Cache $cache = null) : AbstractApp
    {
        $cacheNs = (string) filemtime($appMeta->appDir . '/src');
        $injector = new AppInjector($appMeta->name, $contexts, $appMeta, $cacheNs);
        $cache = $cache instanceof Cache ? $cache : $injector->getCachedInstance(Cache::class);
        $appId = $appMeta->name . $contexts . $cacheNs;
        $app = $cache->fetch($appId);
        if ($app instanceof AbstractApp) {
            return $app;
        }
        $injector->clear();
        $app = $injector->getCachedInstance(AppInterface::class);
        $cache->save($appId, $app);

        return $app;
    }
}
