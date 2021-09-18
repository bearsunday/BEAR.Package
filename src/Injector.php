<?php

declare(strict_types=1);

namespace BEAR\Package;

use BEAR\AppMeta\Meta;
use BEAR\Package\Injector\FileUpdate;
use BEAR\Package\Module\ScriptinjectorModule;
use BEAR\Sunday\Extension\Application\AppInterface;
use Ray\Compiler\Annotation\Compile;
use Ray\Compiler\ScriptInjector;
use Ray\Di\Injector as RayInjector;
use Ray\Di\InjectorInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\ApcuAdapter;
use Symfony\Component\Cache\Adapter\ChainAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\CacheInterface;

use function assert;
use function is_bool;
use function is_dir;
use function mkdir;
use function str_replace;

final class Injector
{
    /**
     * Serialized injector instances
     *
     * @var array<string, InjectorInterface>
     */
    private static $instances;

    /**
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }

    public static function getInstance(string $appName, string $context, string $appDir, ?CacheInterface $cache = null): InjectorInterface
    {
        $injectorId = str_replace('\\', '_', $appName) . $context;
        if (isset(self::$instances[$injectorId])) {
            return self::$instances[$injectorId];
        }

        $meta = new Meta($appName, $context, $appDir);
        $cache = $cache ?? new ChainAdapter([new ApcuAdapter($injectorId), new FilesystemAdapter($injectorId, 0, $meta->tmpDir . '/injector')]);
        assert($cache instanceof AdapterInterface);
        /** @psalm-suppress all */
        [$injector, $fileUpdate] = $cache->getItem($injectorId)->get();
        $isCacheableInjector = $injector instanceof ScriptInjector || ($injector instanceof InjectorInterface && $fileUpdate instanceof FileUpdate && $fileUpdate->isNotUpdated($meta));
        if (! $isCacheableInjector) {
            $injector = self::factory($meta, $context);
            $cache->save($cache->getItem($injectorId)->set([$injector, new FileUpdate($meta)]));
        }

        self::$instances[$injectorId] = $injector;

        return $injector;
    }

    private static function factory(Meta $meta, string $context): InjectorInterface
    {
        $scriptDir = $meta->tmpDir . '/di';
        ! is_dir($scriptDir) && ! @mkdir($scriptDir) && ! is_dir($scriptDir);
        $module = (new Module())($meta, $context, '');
        $injector = new RayInjector($module, $scriptDir);
        $isProd = $injector->getInstance('', Compile::class);
        assert(is_bool($isProd));
        if ($isProd) {
            $injector = new ScriptInjector($scriptDir, static function () use ($scriptDir, $module) {
                return new ScriptinjectorModule($scriptDir, $module);
            });
        }

        $injector->getInstance(AppInterface::class);

        return $injector;
    }
}
