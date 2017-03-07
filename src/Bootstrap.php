<?php
/**
 * This file is part of the BEAR.Package package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Package;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\AppMeta\AppMeta;
use BEAR\Package\Exception\InvalidContextException;
use BEAR\Sunday\Extension\Application\AbstractApp;
use Doctrine\Common\Cache\ApcuCache;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Cache\VoidCache;
use Ray\Compiler\DiCompiler;
use Ray\Compiler\Exception\NotCompiled;
use Ray\Compiler\ScriptInjector;
use Ray\Di\AbstractModule;

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
     *
     * @return AbstractApp
     */
    public function getApp($name, $contexts)
    {
        return $this->newApp(new AppMeta($name, $contexts), $contexts);
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
        $isProd = is_int(strpos($contexts, 'prod'));
        $app = $cache->fetch($appId);
        if ($app && $isProd) {
            return $app;
        }
        $app = $this->getAppInstance($appMeta, $contexts);
        $cache->save($appId, $app);

        return $app;
    }

    /**
     * @param AbstractAppMeta $appMeta
     * @param string          $contexts
     *
     * @return AbstractApp
     */
    private function getAppInstance(AbstractAppMeta $appMeta, $contexts)
    {
        $module = $this->newModule($appMeta, $contexts);
        $module->override(new AppMetaModule($appMeta));
        try {
            $app = (new ScriptInjector($appMeta->tmpDir))->getInstance(AbstractApp::class);
        } catch (NotCompiled $e) {
            $compiler = new DiCompiler($module, $appMeta->tmpDir);
            $compiler->compile();
            $app = (new ScriptInjector($appMeta->tmpDir))->getInstance(AbstractApp::class);
        }

        return $app;
    }

    /**
     * Return configured module
     *
     * @param AbstractAppMeta $appMeta
     * @param string          $contexts
     *
     * @return AbstractModule
     */
    private function newModule(AbstractAppMeta $appMeta, $contexts)
    {
        $contextsArray = array_reverse(explode('-', $contexts));
        $module = new AbstractAppModule($appMeta);
        foreach ($contextsArray as $context) {
            $class = $appMeta->name . '\Module\\' . ucwords($context) . 'Module';
            if (!class_exists($class)) {
                $class = 'BEAR\Package\Context\\' . ucwords($context) . 'Module';
            }
            if (! is_a($class, AbstractModule::class, true)) {
                throw new InvalidContextException($class);
            }
            /* @var $module AbstractModule */
            $module = new $class($module);
        }

        return $module;
    }

    /**
     * Return contextual cache
     *
     * @param AbstractAppMeta $appMeta
     * @param string          $contexts
     *
     * @return ApcuCache|FilesystemCache|VoidCache
     */
    private function getCache(AbstractAppMeta $appMeta, $contexts)
    {
        $isProd = is_int(strpos($contexts, 'prod'));
        if ($isProd) {
            if (function_exists('apcu_fetch')) {
                return new ApcuCache;
            }

            return new FilesystemCache($appMeta->tmpDir);
        }

        return new VoidCache;
    }
}
