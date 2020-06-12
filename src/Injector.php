<?php

declare(strict_types=1);

namespace BEAR\Package;

use BEAR\AppMeta\Meta;
use BEAR\Package\Annotation\DiCompile;
use BEAR\Package\Context\Provider\ProdCacheProvider;
use BEAR\Package\Provide\Boot\ScriptinjectorModule;
use BEAR\Sunday\Extension\Application\AppInterface;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\ChainCache;
use Ray\Compiler\ScriptInjector;
use Ray\Di\Injector as RayInjector;
use Ray\Di\InjectorInterface;

final class Injector
{
    /**
     * Serialized injector instances
     *
     * @var array<InjectorInterface>
     */
    private static $instances;

    private function __construct()
    {
    }

    public static function getInstance(string $appName, string $context, string $appDir, string $cacheNamespace = '') : InjectorInterface
    {
        $injectorId = $appName . $context . $cacheNamespace;
        if (isset(self::$instances[$injectorId])) {
            return self::$instances[$injectorId];
        }
        $meta = new Meta($appName, $context, $appDir);
        $cache = (new ProdCacheProvider($meta, $injectorId))->get();
        /** @var ?InjectorInterface $cachedInjector */
        $cachedInjector = $cache->fetch(InjectorInterface::class);
        $injector = $cachedInjector instanceof InjectorInterface ? $cachedInjector : self::factory($meta, $context, $cacheNamespace, $cache);
        self::$instances[$injectorId] = $injector;

        return $injector;
    }

    private static function factory(Meta $meta, string $context, string $cacheNamespace, ChainCache $cache) : InjectorInterface
    {
        $scriptDir = $meta->tmpDir . '/di';
        ! is_dir($scriptDir) && ! @mkdir($scriptDir) && ! is_dir($scriptDir);
        $module = (new Module)($meta, $context, $cacheNamespace);
        $rayInjector = new RayInjector($module, $scriptDir);
        /** @var bool $isProd */
        $isProd = $rayInjector->getInstance('', DiCompile::class);
        if (! $isProd) {
            $rayInjector->getInstance(AppInterface::class);

            return $rayInjector;
        }
        $scriptInjector = new ScriptInjector($scriptDir, function () use ($scriptDir, $module) {
            return new ScriptinjectorModule($scriptDir, $module);
        });
        $scriptInjector->getInstance(AppInterface::class); // cache App as a singleton
        $cache->save(InjectorInterface::class, $scriptInjector);

        return $scriptInjector;
    }
}
