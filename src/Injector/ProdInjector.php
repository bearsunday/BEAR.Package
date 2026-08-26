<?php

declare(strict_types=1);

namespace BEAR\Package\Injector;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\Package\Compiler\CompileSteps;
use BEAR\Package\Exception\NotCompiledException;
use BEAR\Package\Types;
use BEAR\Sunday\Extension\Application\AppInterface;
use Ray\Compiler\CompiledInjector;
use Ray\Compiler\Compiler;
use Ray\Di\AbstractModule;
use Ray\Di\Injector as RayInjector;
use Ray\Di\InjectorInterface;

use function dirname;
use function error_log;
use function file_exists;
use function is_dir;
use function is_writable;
use function sprintf;
use function str_starts_with;

/**
 * @psalm-import-type Context from Types
 * @psalm-import-type ScriptDir from Types
 */
final class ProdInjector
{
    /**
     * Boot from AOT scripts when a compile marker is present; otherwise compile on demand.
     *
     * A marked build is returned unverified; resolving through it is what reports a broken one.
     * An unwritable tree is treated as holding no build rather than failing the compile.
     * Of the compile command's pipeline only the steps are mirrored here (not class meta info
     * or preload); steps resolve through a module injector, as no script is emitted for them.
     *
     * @param ScriptDir $scriptDir
     * @param Context   $context
     *
     * @see CompileMarker for what the marker does and does not guarantee
     */
    public static function create(AbstractModule $module, string $scriptDir, AbstractAppMeta $meta, string $context): InjectorInterface
    {
        if (CompileMarker::matches($scriptDir, $meta->name, $context)) {
            return new CompiledInjector($scriptDir);
        }

        if (! self::canWrite($scriptDir)) {
            throw new NotCompiledException($scriptDir);
        }

        (new Compiler())->compile($module, $scriptDir);
        (new RayInjector($module, $scriptDir))->getInstance(CompileSteps::class)($meta->buildDir);
        CompileMarker::write($scriptDir, $meta->name, $context);
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
}
