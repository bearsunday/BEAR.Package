<?php

declare(strict_types=1);

namespace BEAR\Package\Injector;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\Package\Compiler\CompileSteps;
use BEAR\Package\Exception\CompiledForAnotherWriteDirException;
use BEAR\Package\Module;
use BEAR\Package\Module\ResourceObjectModule;
use BEAR\Package\Types;
use BEAR\Sunday\Extension\Application\AppInterface;
use Ray\Compiler\Annotation\Compile;
use Ray\Compiler\CompiledInjector;
use Ray\Compiler\Compiler;
use Ray\Compiler\Exception\Unbound;
use Ray\Di\AbstractModule;
use Ray\Di\Injector as RayInjector;
use Ray\Di\InjectorInterface;

use function dirname;
use function error_log;
use function file_exists;
use function hash;
use function is_dir;
use function is_writable;
use function mkdir;
use function sprintf;
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

    /**
     * Return an injector, reusing the instances this process already built.
     *
     * The AOT scripts are asked whether they are a prod build, because assembling a module
     * tree to find that out is the cost this path exists to avoid.
     *
     * @param Context $context
     */
    public static function getInstance(AbstractAppMeta $meta, string $context): InjectorInterface
    {
        // Both directories: one app+context can be booted with different writable ones, and two
        // trees can be booted with the same one.
        $injectorId = str_replace('\\', '_', $meta->name) . $context . '-' . hash('xxh128', $meta->appDir . "\n" . $meta->tmpDir);
        if (isset(self::$instances[$injectorId])) {
            return self::$instances[$injectorId];
        }

        $scriptDir = self::scriptDir($meta, null);
        $injector = CompileMarker::matches($scriptDir, $meta->tmpDir) ? self::aotInjector($scriptDir) : null;

        return self::$instances[$injectorId] = $injector ?? self::factory($meta, $context);
    }

    /**
     * The compiled container, when a prod compile is what produced it.
     *
     * A marker can outlive the build it describes, and a tree that once compiled a
     * per-request context keeps its var/build/{context}/di: serving that from AOT would
     * freeze every source change behind it. DiCompileModule is what records the answer, so
     * the scripts carry it and no module tree has to be assembled to read it.
     *
     * @param ScriptDir $scriptDir
     */
    private static function aotInjector(string $scriptDir): InjectorInterface|null
    {
        $injector = new CompiledInjector($scriptDir);
        try {
            /** @psalm-suppress ArgumentTypeCoercion, MixedAssignment */
            $isProd = $injector->getInstance('', Compile::class);
        } catch (Unbound) {
            return null;
        }

        return $isProd === true ? $injector : null;
    }

    /**
     * Return an injector, optionally with an override module applied.
     *
     * @param Context $context
     */
    public static function factory(AbstractAppMeta $meta, string $context, AbstractModule|null $overrideModule = null): InjectorInterface
    {
        $scriptDir = self::ensureScriptDir($meta, $overrideModule);
        $module = self::module($meta, $context, $overrideModule);
        if (self::isProd($module)) {
            return self::prodInjector($module, $scriptDir, $meta, $context);
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

    /**
     * Whether $context boots from compiled scripts rather than assembling per request.
     *
     * @param Context $context
     */
    public static function isCompiled(AbstractAppMeta $meta, string $context): bool
    {
        return self::isProd(self::module($meta, $context, null));
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
     * Boot from AOT scripts when a compile marker is present; otherwise compile on demand.
     *
     * Broken scripts under a marker are left to throw: a deploy error, not a cold start. A
     * tree that cannot be written is told what the mismatch was instead of failing on it.
     *
     * Of the compile command's pipeline only the steps are mirrored here; class meta info and
     * preload are not. Steps resolve through a module injector, not the AOT one: their classes
     * are compile-time collaborators and no script is emitted for them.
     *
     * @param ScriptDir $scriptDir
     * @param Context   $context
     *
     * @see CompileMarker for what the marker does and does not guarantee
     */
    private static function prodInjector(AbstractModule $module, string $scriptDir, AbstractAppMeta $meta, string $context): InjectorInterface
    {
        if (CompileMarker::matches($scriptDir, $meta->tmpDir)) {
            $injector = new CompiledInjector($scriptDir);
            /** @psalm-suppress InvalidArgument */
            $injector->getInstance(AppInterface::class);

            return $injector;
        }

        if (! self::canWrite($scriptDir)) {
            throw new CompiledForAnotherWriteDirException($scriptDir, CompileMarker::read($scriptDir)?->tmpDir, $meta->tmpDir);
        }

        (new Compiler())->compile($module, $scriptDir);
        (new RayInjector($module, $scriptDir))->getInstance(CompileSteps::class)($meta->buildDir);
        CompileMarker::write($scriptDir, $meta->name, $context, $meta->tmpDir);
        $injector = new CompiledInjector($scriptDir);
        /** @psalm-suppress InvalidArgument */
        $injector->getInstance(AppInterface::class);
        self::logOnDemandCompile($scriptDir);

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
     * Report an on-demand compile to the server's log.
     *
     * Not the application logger - the report is due while its container is still being built.
     * Not trigger_error() - display_errors would put it in the response body.
     */
    private static function logOnDemandCompile(string $scriptDir): void
    {
        error_log(sprintf(
            'Compiled DI scripts on demand in "%s". See https://bearsunday.github.io/manuals/1.0/en/production.html#compilation-recommended',
            $scriptDir,
        ));
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
