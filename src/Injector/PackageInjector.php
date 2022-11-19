<?php

declare(strict_types=1);

namespace BEAR\Package\Injector;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\Package\LazyModule;
use BEAR\Package\Module;
use BEAR\Sunday\Extension\Application\AppInterface;
use Ray\Compiler\Annotation\Compile;
use Ray\Compiler\CompileInjector;
use Ray\Compiler\ScriptInjector;
use Ray\Di\AbstractModule;
use Ray\Di\Injector as RayInjector;
use Ray\Di\InjectorInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Contracts\Cache\CacheInterface;

use function assert;
use function is_bool;
use function is_dir;
use function mkdir;
use function str_replace;

final class PackageInjector
{
    /**
     * Serialized injector instances
     *
     * @var array<string, InjectorInterface>
     */
    private static array $instances;

    /** @codeCoverageIgnore */
    private function __construct()
    {
    }

    public static function getInstance(AbstractAppMeta $meta, string $context, CacheInterface|null $cache): InjectorInterface
    {
        $injectorId = str_replace('\\', '_', $meta->name) . $context;
        if (isset(self::$instances[$injectorId])) {
            return self::$instances[$injectorId];
        }

        assert($cache instanceof AdapterInterface);
        /** @psalm-suppress all */
        [$injector, $fileUpdate] = $cache->getItem($injectorId)->get(); // @phpstan-ignore-line
        $isCacheableInjector = $injector instanceof ScriptInjector || ($injector instanceof InjectorInterface && $fileUpdate instanceof FileUpdate && $fileUpdate->isNotUpdated($meta));
        if (! $isCacheableInjector) {
            $injector = self::factory($meta, $context);
            $cache->save($cache->getItem($injectorId)->set([$injector, new FileUpdate($meta)]));
        }

        self::$instances[$injectorId] = $injector;

        return $injector;
    }

    public static function factory(AbstractAppMeta $meta, string $context, AbstractModule|null $overideModule = null): InjectorInterface
    {
        $scriptDir = $meta->tmpDir . '/di';
        ! is_dir($scriptDir) && ! @mkdir($scriptDir) && ! is_dir($scriptDir);
        $module = (new Module())($meta, $context);

        if ($overideModule instanceof AbstractModule) {
            $module->override($overideModule);
        }

        $injector = new RayInjector($module, $scriptDir);
        $isProd = $injector->getInstance('', Compile::class);
        assert(is_bool($isProd));
        if ($isProd) {
            $injector = new CompileInjector($scriptDir, new LazyModule($meta, $context, $scriptDir));
        }

        $injector->getInstance(AppInterface::class);

        return $injector;
    }
}
