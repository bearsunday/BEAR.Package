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
use Ray\Di\AbstractModule;
use Ray\Di\Injector as RayInjector;
use Ray\Di\InjectorInterface;

use function hash;
use function is_dir;
use function mkdir;
use function preg_replace;
use function str_replace;

/**
 * @psalm-import-type AppDir from Types
 * @psalm-import-type Context from Types
 * @psalm-import-type ScriptDir from Types
 */
final class PackageInjector
{
    private const string ANONYMOUS_CLASS_COUNTER = '/\$[0-9a-f]+$/';

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
     * Return an injector, reusing the instances this process already built.
     *
     * A current marker means the compiled scripts are this deployment's own, so they are the
     * injector. The module tree is built only when there is no such build to boot from - it
     * is what compiles one.
     *
     * @param Context $context
     */
    public static function getInstance(AbstractAppMeta $meta, string $context): InjectorInterface
    {
        // Keyed by appDir: two checkouts of one application must not share an injector.
        $injectorId = str_replace('\\', '_', $meta->name) . $context . '-' . hash('xxh128', $meta->appDir);
        if (isset(self::$instances[$injectorId])) {
            return self::$instances[$injectorId];
        }

        $scriptDir = self::scriptDir($meta, null, false);
        if (CompileMarker::matches($scriptDir, $meta->name, $context)) {
            return self::$instances[$injectorId] = new CompiledInjector($scriptDir);
        }

        return self::$instances[$injectorId] = self::factory($meta, $context);
    }

    /**
     * Return an injector, optionally with an override module applied.
     *
     * @param Context $context
     */
    public static function factory(AbstractAppMeta $meta, string $context, AbstractModule|null $overrideModule = null): InjectorInterface
    {
        $module = self::module($meta, $context, $overrideModule);
        $isProd = self::isProd($module);
        $scriptDir = self::ensureScriptDir($meta, $overrideModule, $isProd);
        if ($isProd) {
            return ProdInjector::create($module, $scriptDir, $meta, $context);
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

    /** @return ScriptDir */
    private static function ensureScriptDir(AbstractAppMeta $meta, AbstractModule|null $overrideModule, bool $isProd): string
    {
        $scriptDir = self::scriptDir($meta, $overrideModule, $isProd);
        if (! is_dir($scriptDir)) {
            @mkdir($scriptDir, 0777, true);
        }

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
     * Resolve the script directory for AOP proxies / compiled container.
     *
     * Override injectors use a subdirectory keyed on the override module's class so they never
     * share on-disk artifacts with the default injector for the same app+context (#478).
     *
     * @return ScriptDir
     */
    private static function scriptDir(AbstractAppMeta $meta, AbstractModule|null $overrideModule, bool $isProd): string
    {
        $scriptDir = $meta->buildDir . '/di';
        if (! $overrideModule instanceof AbstractModule) {
            return $scriptDir;
        }

        // Dev drops the per-entry-point counter: its AOP files are named by their bindings.
        // Prod keeps it: it compiles here, and the compile marker cannot tell overrides apart.
        $class = $overrideModule::class;
        $key = $isProd ? $class : (string) preg_replace(self::ANONYMOUS_CLASS_COUNTER, '', $class);

        return $scriptDir . '/' . hash('xxh128', $key);
    }

    /**
     * Detect prod without a RayInjector — that would mutate $module via AOP weaving (#467).
     *
     * DiCompileModule always binds Compile, so an unbound one means the module did not come
     * from Module: a programming error, not a context to treat as dev.
     */
    private static function isProd(AbstractModule $module): bool
    {
        return (bool) $module->getContainer()->getInstance('', Compile::class);
    }
}
