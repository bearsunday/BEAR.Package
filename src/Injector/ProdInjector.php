<?php

declare(strict_types=1);

namespace BEAR\Package\Injector;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\Package\Compiler\CompileSteps;
use BEAR\Package\Types;
use BEAR\Sunday\Extension\Application\AppInterface;
use Ray\Compiler\CompiledInjector;
use Ray\Compiler\Compiler;
use Ray\Di\AbstractModule;
use Ray\Di\Injector as RayInjector;
use Ray\Di\InjectorInterface;

use function error_log;
use function sprintf;

/**
 * @psalm-import-type Context from Types
 * @psalm-import-type ScriptDir from Types
 */
final class ProdInjector
{
    /**
     * Boot from AOT scripts when a compile marker is present; otherwise compile on demand.
     *
     * A build under a marker is returned unwalked: the request resolves through it anyway.
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
    public static function create(AbstractModule $module, string $scriptDir, AbstractAppMeta $meta, string $context): InjectorInterface
    {
        if (CompileMarker::matches($scriptDir, $meta->name, $context)) {
            return new CompiledInjector($scriptDir);
        }

        (new Compiler())->compile($module, $scriptDir);
        $moduleInjector = new RayInjector($module, $scriptDir);
        $moduleInjector->getInstance(CompileSteps::class)($meta->buildDir);
        $boundMeta = $moduleInjector->getInstance(AbstractAppMeta::class);
        CompileMarker::write($scriptDir, $meta->name, $context, $boundMeta->tmpDir);
        $injector = new CompiledInjector($scriptDir);
        /** @psalm-suppress InvalidArgument */
        $injector->getInstance(AppInterface::class);
        self::logOnDemandCompile($scriptDir);

        return $injector;
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
