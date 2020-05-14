<?php

declare(strict_types=1);

namespace BEAR\Package;

use BEAR\AppMeta\Meta;
use BEAR\Package\Context\Provider\ProdCacheProvider;
use BEAR\Package\Provide\Boot\ScriptinjectorModule;
use BEAR\Sunday\Extension\Application\AppInterface;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\ChainCache;
use Ray\Compiler\ScriptInjector;
use Ray\Di\Injector as RayInjector;
use Ray\Di\InjectorInterface;

final class Injector implements InjectorInterface
{
    /**
     * Injector cache for unit testing
     *
     * @var array<InjectorInterface>
     */
    private static $cache;

    /**
     * @var InjectorInterface
     */
    private $injector;

    public function __construct(string $appName, string $context, string $appDir, string $cacheNamespace = '')
    {
        $meta = new Meta($appName, $context, $appDir);
        $scriptDir = $meta->tmpDir . '/di';
        ! is_dir($scriptDir) && ! @mkdir($scriptDir) && ! is_dir($scriptDir);
        $cacheDir = $meta->tmpDir . '/cache';
        ! is_dir($cacheDir) && ! @mkdir($cacheDir) && ! is_dir($cacheDir);
        $id = $context . $cacheNamespace;
        if (isset(self::$cache[$id])) {
            $this->injector = unserialize(self::$cache[$id]); // activation

            return;
        }
        $cache = (new ProdCacheProvider($meta, $cacheNamespace))->get();
        $injector = $cache->fetch(InjectorInterface::class);
        $this->injector = $injector instanceof InjectorInterface ? $injector : $this->getInjector($meta, $context, $cacheNamespace, $cache, $scriptDir);
        $this->injector->getInstance(AppInterface::class); // cache App as a singleton
        self::$cache[$id] = serialize($this->injector);
    }

    /**
     * {@inheritdoc}
     */
    public function getInstance($interface, $name = '')
    {
        return $this->injector->getInstance($interface, $name);
    }

    private function getInjector(Meta $meta, string $context, string $cacheNamespace, ChainCache $firstCache, string $scriptDir) : InjectorInterface
    {
        $module = (new Module)($meta, $context, $cacheNamespace);
        $rayInjector = new RayInjector($module, $scriptDir);
        $isDev = $rayInjector->getInstance(Cache::class) instanceof ArrayCache;
        if ($isDev) {
            return $rayInjector;
        }
        $injector = new ScriptInjector($scriptDir, function () use ($scriptDir, $module) {
            return new ScriptinjectorModule($scriptDir, $module);
        });
        $injector->getInstance(AppInterface::class); // cache App as a singleton
        $firstCache->save(InjectorInterface::class, $injector);

        return $injector;
    }
}
