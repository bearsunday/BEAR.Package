<?php

declare(strict_types=1);

namespace BEAR\Package\Injector;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\Package\Exception\NotCompiledException;
use BEAR\Package\Module;
use BEAR\Package\Module\ResourceObjectModule;
use BEAR\Package\Types;
use BEAR\Sunday\Extension\Application\AppInterface;
use Ray\Compiler\Annotation\Compile;
use Ray\Compiler\CompiledInjector;
use Ray\Di\AbstractModule;
use Ray\Di\Injector as RayInjector;
use Ray\Di\InjectorInterface;

use function dirname;
use function file_exists;
use function hash;
use function is_dir;
use function is_writable;
use function mkdir;
use function str_replace;
use function str_starts_with;

/**
 * @psalm-import-type AppDir from Types
 * @psalm-import-type Context from Types
 * @psalm-import-type ScriptDir from Types
 */
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

    /** @param Context $context */
    public static function getInstance(AbstractAppMeta $meta, string $context): InjectorInterface
    {
        $injectorId = str_replace('\\', '_', $meta->name) . $context . '-' . hash('xxh128', $meta->appDir);
        if (isset(self::$instances[$injectorId])) {
            return self::$instances[$injectorId];
        }

        $scriptDir = self::scriptDir($meta, null);
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
        $scriptDir = self::ensureScriptDir($meta, $overrideModule);
        // Before the module tree, whose own failure inside an archive would mask this one.
        if (! self::canWrite($scriptDir) && ! CompileMarker::matches($scriptDir, $meta->name, $context)) {
            throw new NotCompiledException($scriptDir);
        }

        $module = self::module($meta, $context, $overrideModule);
        if (self::isProd($module)) {
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
    private static function ensureScriptDir(AbstractAppMeta $meta, AbstractModule|null $overrideModule): string
    {
        $scriptDir = self::scriptDir($meta, $overrideModule);
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
     * Whether a compile could write to $dir, which a first boot has not created yet.
     *
     * Answered by the nearest existing ancestor, and only by an ancestor: dirname() leaves the
     * path for the working directory once it runs out of them, and the cwd knows nothing here.
     */
    private static function canWrite(string $dir): bool
    {
        for ($path = $dir; ! file_exists($path); $path = $parent) {
            $parent = dirname($path);
            if (! str_starts_with($dir, $parent)) {
                return false;
            }
        }

        return is_dir($path) && is_writable($path);
    }

    /**
     * Resolve the script directory for AOP proxies / compiled container.
     *
     * Override injectors use a class-name hash subdirectory so they never share
     * on-disk artifacts with the default injector for the same app+context (#478).
     *
     * @return ScriptDir
     */
    private static function scriptDir(AbstractAppMeta $meta, AbstractModule|null $overrideModule): string
    {
        $scriptDir = $meta->buildDir . '/di';
        if ($overrideModule instanceof AbstractModule) {
            $scriptDir .= '/' . hash('xxh128', $overrideModule::class);
        }

        return $scriptDir;
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
