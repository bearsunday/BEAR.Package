<?php

declare(strict_types=1);

namespace BEAR\Package\Injector;

use BEAR\Package\Types;

/**
 * Where a compile puts what it produced.
 *
 * One tree holds one build: nothing here is named after the context compiled for, so a second
 * compile replaces the first. What a run writes stays under the context's own tmp directory.
 *
 * @psalm-import-type AppDir from Types
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
     * @param AppDir $appDir
     *
     * @return ScriptDir
     */
    public static function dir(string $appDir): string
    {
        return self::buildDir($appDir) . '/di';
    }

    /**
     * @param AppDir $appDir
     *
     * @return BuildDir
     */
    public static function buildDir(string $appDir): string
    {
        return $appDir . '/var/build';
    }
}
