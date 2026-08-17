<?php

declare(strict_types=1);

namespace BEAR\Package\Injector;

use BEAR\Package\Types;

/**
 * @psalm-import-type AppDir from Types
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
     * Compiled DI scripts, never under a write directory: the deployment artifact carries them.
     *
     * @param AppDir  $appDir
     * @param Context $context
     *
     * @return ScriptDir
     */
    public static function dir(string $appDir, string $context): string
    {
        return $appDir . '/var/build/' . $context . '/di';
    }
}
