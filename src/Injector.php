<?php

declare(strict_types=1);

namespace BEAR\Package;

use BEAR\AppMeta\Meta;
use BEAR\Package\Module\ScriptinjectorModule;
use BEAR\Sunday\Extension\Application\AppInterface;
use Doctrine\Common\Cache\CacheProvider;
use Doctrine\Common\Cache\PhpFileCache;
use function is_dir;
use function mkdir;
use Ray\Compiler\Annotation\Compile;
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

    /**
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }

    public static function getInstance(string $appName, string $context, string $appDir, CacheProvider $cache = null) : InjectorInterface
    {
        $injectorId = $appName . $context;
        if (isset(self::$instances[$injectorId])) {
            return self::$instances[$injectorId];
        }
        $meta = new Meta($appName, $context, $appDir);
        $cache = $cache ?? new PhpFileCache($meta->tmpDir . '/injector');
        $cache->setNamespace($injectorId);
        /** @var ?InjectorInterface $cachedInjector */
        $cachedInjector = $cache->fetch(InjectorInterface::class);
        if ($cachedInjector instanceof InjectorInterface) {
            return $cachedInjector;
        }
        $injector = self::factory($meta, $context);
        $injector->getInstance(AppInterface::class);
        if ($injector instanceof ScriptInjector) {
            $cache->save(InjectorInterface::class, $injector);
        }
        self::$instances[$injectorId] = $injector;

        return $injector;
    }

    private static function factory(Meta $meta, string $context) : InjectorInterface
    {
        $scriptDir = $meta->tmpDir . '/di';
        ! is_dir($scriptDir) && ! @mkdir($scriptDir) && ! is_dir($scriptDir);
        $module = (new Module)($meta, $context, '');
        $rayInjector = new RayInjector($module, $scriptDir);
        /** @var bool $isProd */
        $isProd = $rayInjector->getInstance('', Compile::class);
        if ($isProd) {
            return new ScriptInjector($scriptDir, function () use ($scriptDir, $module) {
                return new ScriptinjectorModule($scriptDir, $module);
            });
        }

        return $rayInjector;
    }
}
