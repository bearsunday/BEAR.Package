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
use Psr\Log\LoggerInterface;
use Ray\Compiler\ScriptInjector;

/**
 * Bootstrap
 *
 * Create an app object that contains all the objects used in the bootstrap script　The bootstrap script uses the public
 * property of $ app to run the application.
 *
 * AppModule knows the binding of all interfaces. Other context modules override bindings on the interface. For example,
 * `app` binds JsonRenderer and outputs JSON. In` html-prod`, HtmlModule overwrites the binding on TwigRenderer and
 * outputs html.
 */
final class Bootstrap
{
    /**
     * Return application instance by name and contexts
     *
     * Use newApp() instead for your own AppMeta and Cache.
     *
     * @param string $name     application name    'koriym\blog' (vendor\package)
     * @param string $contexts application context 'prd-html-app'
     * @param string $appDir   application path
     *
     * @return AbstractApp
     */
    public function getApp($name, $contexts, $appDir = null)
    {
        return $this->newApp(new AppMeta($name, $contexts, $appDir), $contexts);
    }

    /**
     * Return application instance by AppMeta and Cache
     *
     * @param AbstractAppMeta $appMeta
     * @param string          $contexts
     * @param Cache           $cache
     *
     * @return AbstractApp
     */
    public function newApp(AbstractAppMeta $appMeta, $contexts, Cache $cache = null)
    {
        $cache = $cache ?: $this->getCache($appMeta, $contexts);
        $appId = $appMeta->name . $contexts;
        list($app, $scriptInjector) = $cache->fetch($appId); // $scriptInjector set cached single instance in wakeup
        if ($app && $app instanceof AbstractApp) {
            return $app;
        }
        $app = (new AppInjector($appMeta->name, $contexts))->getInstance(AppInterface::class);
        $scriptInjector = new ScriptInjector($appMeta->tmpDir);
        // save singleton instance cache
        $scriptInjector->getInstance(Reader::class);
        $scriptInjector->getInstance(Cache::class);
        $scriptInjector->getInstance(LoggerInterface::class);
        $scriptInjector->getInstance(ResourceInterface::class);
        $log = sprintf('%s/context.%s.log', $appMeta->logDir, $contexts);
        file_put_contents($log, print_r($app, true));
        $cache->save($appId, [$app, $scriptInjector]);

        return $app;
    }

    /**
     * Return contextual cache
     *
     * @param AbstractAppMeta $appMeta
     * @param string          $contexts
     *
     * @return Cache
     */
    private function getCache(AbstractAppMeta $appMeta, $contexts)
    {
        $isDeveop = ! is_int(strpos($contexts, 'prod'));
        if ($isDeveop) {
            return new VoidCache;
        }
        $fileCache = new FilesystemCache($appMeta->tmpDir);
        if (function_exists('apcu_fetch')) {
            return new ChainCache([new ApcuCache, $fileCache]); // @codeCoverageIgnore
        }

        return $fileCache; // @codeCoverageIgnore
    }
}
