<?php

declare(strict_types=1);

namespace BEAR\Package\Injector;

use BEAR\Package\Types;

/**
 * The subdirectory of a build a compile writes its DI scripts into.
 *
 * Where the build directory is belongs to app-meta: read `$meta->buildDir`, or take it from
 * whoever held the Meta.
 *
 * @psalm-import-type BuildDir from Types
 * @psalm-import-type ScriptDir from Types
 */
final class CompiledScripts
{
    /** @codeCoverageIgnore */
    private function __construct()
    {
    }

    /**
     * @param BuildDir $buildDir
     *
     * @return ScriptDir
     */
    public static function dir(string $buildDir): string
    {
        return $buildDir . '/di';
    }
}
