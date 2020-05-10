<?php

declare(strict_types=1);

namespace BEAR\Package;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\AppMeta\Meta;
use BEAR\Sunday\Extension\Application\AppInterface;
use Doctrine\Common\Cache\ApcuCache;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\ChainCache;
use Doctrine\Common\Cache\FilesystemCache;
use Ray\Di\Bind;
use Ray\Di\Injector as RayInjector;
use Ray\Di\InjectorInterface;

final class Injector implements InjectorInterface
{
    /**
     * @var InjectorInterface
     */
    private $injector;

    public function __construct(string $name, string $context, string $appDir, string $cacheNamespace = '')
    {
        $meta = new Meta($name, $context, $appDir);
        $firstCache = new ChainCache([new ApcuCache, new FilesystemCache($meta->tmpDir)]);
        $firstCache->setNamespace($name . $context . $cacheNamespace);
        $secondCache = $firstCache->fetch(Cache::class); // ArrayCache or ChainCache based on context
        if (! $secondCache instanceof Cache) {
            $freshInjector = $this->createInjector($meta, $context, $cacheNamespace);
            /** @var Cache $secondCache */
            $secondCache = $freshInjector->getInstance(Cache::class);
            $firstCache->save(Cache::class, $secondCache);
            // put App object into container as a singleton
            $freshInjector->getInstance(AppInterface::class);
            $secondCache->save(InjectorInterface::class, $freshInjector);
        }
        /** @var bool|InjectorInterface $cachedInjector */
        $cachedInjector = $secondCache->fetch(InjectorInterface::class);
        if ($cachedInjector instanceof InjectorInterface) {
            $this->injector = $cachedInjector;

            return;
        }
        $this->injector = isset($freshInjector) ? $freshInjector : $this->createInjector($meta, $context, $cacheNamespace);
        $secondCache->save(InjectorInterface::class, $this->injector);
    }

    /**
     * @template T
     *
     * @param class-string<T> $interface
     * @param string          $name
     *
     * @return T
     */
    public function getInstance($interface, $name = '')
    {
        return $this->injector->getInstance($interface, $name);
    }

    private function createInjector(AbstractAppMeta $meta, string $context, string $cacheNamespace) : InjectorInterface
    {
        $module = (new Module)($meta, $context);
        $container = $module->getContainer();
        (new Bind($container, ''))->annotatedWith('cache_namespace')->toInstance($cacheNamespace);

        return new RayInjector($module);
    }
}
