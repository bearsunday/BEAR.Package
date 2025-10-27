<?php

declare(strict_types=1);

namespace BEAR\Package\Injector;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\Package\Module;
use BEAR\Package\Module\ResourceObjectModule;
use BEAR\Package\Types;
use BEAR\Sunday\Extension\Application\AppInterface;
use Ray\Compiler\Annotation\Compile;
use Ray\Compiler\CompiledInjector;
use Ray\Compiler\Compiler;
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
use function trigger_error;

use const E_USER_WARNING;

/** @psalm-import-type Context from Types */
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

    /**
     * Returns an instance of InjectorInterface based on the given parameters
     *
     * @param Context $context
     *
     * - Injector instances are cached in memory and in the cache adapter.
     * - The injector is re-used in subsequent calls in the same context in the unit test.
     */
    public static function getInstance(AbstractAppMeta $meta, string $context, CacheInterface|null $cache): InjectorInterface
    {
        $injectorId = str_replace('\\', '_', $meta->name) . $context;
        if (isset(self::$instances[$injectorId])) {
            return self::$instances[$injectorId];
        }

        assert($cache instanceof AdapterInterface);
        /** @psalm-suppress MixedAssignment, MixedArrayAccess */
        [$injector, $fileUpdate] = $cache->getItem($injectorId)->get(); // @phpstan-ignore-line
        $isCacheableInjector = $injector instanceof ScriptInjector || ($injector instanceof InjectorInterface && $fileUpdate instanceof FileUpdate && $fileUpdate->isNotUpdated($meta));
        if (! $isCacheableInjector) {
            $injector = self::getInjector($meta, $context, $cache, $injectorId);
        }

        self::$instances[$injectorId] = $injector;

        return $injector;
    }

    /**
     * Return an injector instance with the given override module
     *
     * @param Context $context
     *
     * This is useful for testing purposes, where you want to override a module with a mock or stub
     */
    public static function factory(AbstractAppMeta $meta, string $context, AbstractModule|null $overrideModule = null): InjectorInterface
    {
        $scriptDir = $meta->tmpDir . '/di';
        ! is_dir($scriptDir) && ! @mkdir($scriptDir) && ! is_dir($scriptDir);
        $module = (new Module())($meta, $context);

        if ($overrideModule instanceof AbstractModule) {
            $module->override($overrideModule);
        }

        // Bind ResourceObject
        $module->install(new ResourceObjectModule($meta->getResourceListGenerator()));

        $injector = new RayInjector($module, $scriptDir);
        $isProd = $injector->getInstance('', Compile::class);
        assert(is_bool($isProd));
        if ($isProd) {
            $compiler = new Compiler();
            $compiler->compile($module, $scriptDir);
            $injector = new CompiledInjector($scriptDir);
        }

        /** @psalm-suppress InvalidArgument */
        $injector->getInstance(AppInterface::class);

        return $injector;
    }

    /** @param Context $context */
    private static function getInjector(AbstractAppMeta $meta, string $context, AdapterInterface $cache, string $injectorId): InjectorInterface
    {
        $injector = self::factory($meta, $context);
        $cache->save($cache->getItem($injectorId)->set([$injector, new FileUpdate($meta)]));
        // Check the cache
        if ($cache->getItem($injectorId)->get() === null) {
            trigger_error('Failed to verify the injector cache. See https://github.com/bearsunday/BEAR.Package/issues/418', E_USER_WARNING);
        }

        return $injector;
    }
}
