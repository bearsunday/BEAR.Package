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
        return $this->newApp(new AppMeta($name, $contexts, $appDir), $contexts);
    }

    public function newApp(AbstractAppMeta $appMeta, string $contexts, Cache $cache = null) : AbstractApp
    {
        $injector = new AppInjector($appMeta->name, $contexts, $appMeta);
        $cache = $cache instanceof Cache ? $cache : $injector->getInstance(Cache::class);
        $appId = $appMeta->name . $contexts . filemtime($appMeta->appDir . '/src');
        $app = $cache->fetch($appId);
        if ($app instanceof AbstractApp) {
            return $app;
        }
        $t = microtime(true);
        $injector->clear();
        $app = $injector->getInstance(AppInterface::class);
        $injector->getInstance(Reader::class);
        $injector->getInstance(ResourceInterface::class);
        $cache->save($appId, $app);
        file_put_contents(
            $appMeta->logDir . '/app.log',
            sprintf(
                "%d mssec\n",
                ((microtime(true) - $t) * 1000)
            )
        );

        return $app;
    }
}
