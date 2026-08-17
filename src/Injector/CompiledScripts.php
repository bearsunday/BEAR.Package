<?php

declare(strict_types=1);

namespace BEAR\Package\Injector;

use BEAR\Package\Types;

/**
 * Where a compile puts what it produced.
 *
 * @psalm-import-type AppDir from Types
 * @psalm-import-type BuildDir from Types
 * @psalm-import-type Context from Types
 * @psalm-import-type ScriptDir from Types
 */
final class CompiledScripts
{
    /** @codeCoverageIgnore */
    private function __construct()
    {
    }

    /**
     * @param AppDir  $appDir
     * @param Context $context
     *
     * @return ScriptDir
     */
    public static function dir(string $appDir, string $context): string
    {
        return self::buildDir($appDir, $context) . '/di';
    }

    /**
     * One compile's output.
     *
     * @param AppDir  $appDir
     * @param Context $context
     *
     * @return BuildDir
     */
    public static function buildDir(string $appDir, string $context): string
    {
        return self::buildRoot($appDir) . '/' . $context;
    }

    /**
     * Every context's build directory sits here.
     *
     * @param AppDir $appDir
     *
     * @return non-empty-string
     */
    public static function buildRoot(string $appDir): string
    {
        return $appDir . '/var/build';
    }
}
