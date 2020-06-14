<?php

declare(strict_types=1);

namespace BEAR\Package;

use BEAR\AppMeta\Meta;
use BEAR\Package\Context\Provider\ProdCacheProvider;
use BEAR\Sunday\Extension\Application\AppInterface;
use Ray\Compiler\CachedInjectorFactory;
use Ray\Di\AbstractModule;
use Ray\Di\InjectorInterface;
use function sprintf;

final class Injector
{
    private function __construct()
    {
    }

    public static function getInstance(string $appName, string $context, string $appDir, string $cacheNamespace = '') : InjectorInterface
    {
        $injectorId = $appName . $context . $cacheNamespace;
        $meta = new Meta($appName, $context, $appDir);
        $cache = (new ProdCacheProvider($meta, $injectorId))->get();
        $module = function () use ($meta, $context, $cacheNamespace) : AbstractModule {
            return (new Module)($meta, $context, $cacheNamespace);
        };
        $scriptDir = sprintf('%s/di', $meta->tmpDir);

        return CachedInjectorFactory::getInstance($injectorId, $scriptDir, $module, $cache, [AppInterface::class]);
    }
}
