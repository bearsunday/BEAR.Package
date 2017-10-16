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
     */
    public function getApp(string $name, string $contexts, string $appDir = null) : AbstractApp
    {
        return $this->newApp(new AppMeta($name, $contexts, $appDir), $contexts);
    }

    /**
     * Return cached contextual application instance
     */
    public function newApp(AbstractAppMeta $appMeta, string $contexts, Cache $cache = null) : AbstractApp
    {
        $isCacheable = is_int(strpos($contexts, 'prod-')) || is_int(strpos($contexts, 'stage-'));
        $cache = $cache ?: ($isCacheable ? new ChainCache([new ApcuCache, new FilesystemCache($appMeta->tmpDir)]) : new VoidCache);
        $appId = $appMeta->name . $contexts . filemtime($appMeta->appDir . '/src');
        list($app) = $cache->fetch($appId); // $scriptInjector set cached single instance in wakeup
        if ($app && $app instanceof AbstractApp) {
            return $app;
        }
        list($app, $injector) = $this->getInstance($appMeta, $contexts);
        $cache->save($appId, [$app, $injector]); // save $app with injector to save singleton instance (in ScriptInjector::$singletons)

        return $app;
    }

    private function getInstance(AbstractAppMeta $appMeta, string $contexts) : array
    {
        $app = (new AppInjector($appMeta->name, $contexts))->getInstance(AppInterface::class);
        $injector = new ScriptInjector($appMeta->tmpDir);
        // save singleton instance cache
        $injector->getInstance(Reader::class);
        $injector->getInstance(Cache::class);
        $injector->getInstance(LoggerInterface::class);
        $injector->getInstance(ResourceInterface::class);
        $log = sprintf('%s/context.%s.log', $appMeta->logDir, $contexts);
        file_put_contents($log, print_r($app, true));

        return [$app, $injector];
    }
}
