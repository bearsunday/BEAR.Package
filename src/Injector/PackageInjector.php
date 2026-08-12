<?php

declare(strict_types=1);

namespace BEAR\Package\Injector;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\Package\Module;
use BEAR\Package\Module\ResourceObjectModule;
use BEAR\Package\Types;
use BEAR\Sunday\Extension\Application\AppInterface;
use Psr\Log\LoggerInterface;
use Ray\Compiler\Annotation\Compile;
use Ray\Compiler\CompiledInjector;
use Ray\Compiler\Compiler;
use Ray\Compiler\ScriptInjectorInterface;
use Ray\Di\AbstractModule;
use Ray\Di\Injector as RayInjector;
use Ray\Di\InjectorInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Throwable;

use function assert;
use function hash;
use function is_dir;
use function mkdir;
use function serialize;
use function sprintf;
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

        // Prod: restore compiled injector from cache
        assert($cache instanceof AdapterInterface);
        /** @psalm-suppress MixedAssignment */
        $injector = $cache->getItem($injectorId)->get();
        if ($injector instanceof ScriptInjectorInterface) {
            self::$instances[$injectorId] = $injector;

            return $injector;
        }

        // Dev: always build fresh injector (no FileUpdate check)
        $injector = self::factory($meta, $context);

        // Prod: cache the compiled injector
        if ($injector instanceof ScriptInjectorInterface) {
            $cacheItem = $cache->getItem($injectorId);
            $cache->save($cacheItem->set($injector));
            if ($cache->getItem($injectorId)->get() === null) {
                trigger_error(self::diagnoseCacheFailure($injector, $injectorId), E_USER_WARNING);
            }
        }

        self::$instances[$injectorId] = $injector;

        return $injector;
    }

    /**
     * Return an injector instance with the given override module
     *
     * @param Context $context
     *
     * This is useful for testing purposes, where you want to override a module with a mock or stub.
     * When $overrideModule is given, AOP proxies / the compiled container are written under a
     * subdirectory of tmpDir/di keyed by the override module class, so they do not collide with
     * the default injector for the same app+context.
     */
    public static function factory(AbstractAppMeta $meta, string $context, AbstractModule|null $overrideModule = null): InjectorInterface
    {
        $scriptDir = self::ensureScriptDir($meta, $overrideModule);
        $module = self::module($meta, $context, $overrideModule);
        if (self::isProd($module)) {
            return self::prodInjector($module, $scriptDir);
        }

        return self::rayInjector($module, $scriptDir);
    }

    /**
     * Injector for the compile pipeline: never the AOT branch.
     *
     * factory() would take prodInjector()'s runtime cold path, logging an on-demand compile
     * and writing the marker mid-build. The compile here is not the pass in
     * Compiler::compile(): it populates the scripts FakeRun resolves through, the later pass
     * re-emits them after AOP weaving.
     *
     * @param Context $context
     */
    public static function compileInjector(AbstractAppMeta $meta, string $context): InjectorInterface
    {
        $scriptDir = self::ensureScriptDir($meta, null);
        $module = self::module($meta, $context, null);
        if (self::isProd($module)) {
            (new Compiler())->compile($module, $scriptDir);
        }

        return self::rayInjector($module, $scriptDir);
    }

    /** @param Context $context */
    private static function module(AbstractAppMeta $meta, string $context, AbstractModule|null $overrideModule): AbstractModule
    {
        $module = (new Module())($meta, $context);
        if ($overrideModule instanceof AbstractModule) {
            $module->override($overrideModule);
        }

        // Bind ResourceObject
        $module->install(new ResourceObjectModule($meta->getResourceListGenerator()));

        return $module;
    }

    /** @return non-empty-string */
    private static function ensureScriptDir(AbstractAppMeta $meta, AbstractModule|null $overrideModule): string
    {
        $scriptDir = self::scriptDir($meta, $overrideModule);
        ! is_dir($scriptDir) && ! @mkdir($scriptDir, 0777, true) && ! is_dir($scriptDir);

        return $scriptDir;
    }

    private static function rayInjector(AbstractModule $module, string $scriptDir): InjectorInterface
    {
        $injector = new RayInjector($module, $scriptDir);
        /** @psalm-suppress InvalidArgument */
        $injector->getInstance(AppInterface::class);

        return $injector;
    }

    /**
     * Boot from AOT scripts when a compile marker is present; otherwise compile on demand.
     *
     * A marker with broken scripts is a deploy error, not a recoverable one, so
     * the boot is left to throw instead of falling back to a runtime recompile
     * (which would also die under a read-only filesystem).
     *
     * @param non-empty-string $scriptDir
     *
     * @see CompileMarker for what the marker does and does not guarantee
     */
    private static function prodInjector(AbstractModule $module, string $scriptDir): InjectorInterface
    {
        if (CompileMarker::exists($scriptDir)) {
            $injector = new CompiledInjector($scriptDir);
            /** @psalm-suppress InvalidArgument */
            $injector->getInstance(AppInterface::class);

            return $injector;
        }

        (new Compiler())->compile($module, $scriptDir);
        CompileMarker::write($scriptDir);
        $injector = new CompiledInjector($scriptDir);
        /** @psalm-suppress InvalidArgument */
        $injector->getInstance(AppInterface::class);
        self::logOnDemandCompile($injector, $scriptDir);

        return $injector;
    }

    /**
     * Record the on-demand compile through the application's logger.
     *
     * Not trigger_error(): that reaches the response body when display_errors is on, and any
     * handler converting errors to exceptions would turn this report into a boot failure.
     */
    private static function logOnDemandCompile(InjectorInterface $injector, string $scriptDir): void
    {
        $logger = $injector->getInstance(LoggerInterface::class);
        assert($logger instanceof LoggerInterface);
        $logger->notice('Compiled DI scripts on demand', [
            'scriptDir' => $scriptDir,
            'see' => 'https://bearsunday.github.io/manuals/1.0/en/production.html#compilation-recommended',
        ]);
    }

    /**
     * Resolve the script directory for AOP proxies / compiled container.
     *
     * Override injectors use a class-name hash subdirectory so they never share
     * on-disk artifacts with the default injector for the same app+context (#478).
     *
     * @return non-empty-string
     */
    private static function scriptDir(AbstractAppMeta $meta, AbstractModule|null $overrideModule): string
    {
        $scriptDir = $meta->tmpDir . '/di';
        if ($overrideModule instanceof AbstractModule) {
            $scriptDir .= '/' . hash('xxh128', $overrideModule::class);
        }

        return $scriptDir;
    }

    private static function diagnoseCacheFailure(InjectorInterface $injector, string $injectorId): string
    {
        try {
            serialize($injector);
        } catch (Throwable $e) {
            return sprintf('Failed to cache the injector(%s). Serialization failed: %s', $injectorId, $e->getMessage());
        }

        return sprintf('Failed to cache the injector(%s). The cache adapter could not store the item. See https://github.com/bearsunday/BEAR.Package/issues/418', $injectorId);
    }

    /**
     * Detect prod without a RayInjector — that would mutate $module via AOP weaving (#467).
     *
     * DiCompileModule always binds Compile (false in dev, true in prod) and PackageModule
     * always installs it, so an unbound Compile means the module never came from Module:
     * a programming error, not a context to treat as dev.
     */
    private static function isProd(AbstractModule $module): bool
    {
        return (bool) $module->getContainer()->getInstance('', Compile::class);
    }
}
