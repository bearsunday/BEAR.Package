<?php

declare(strict_types=1);

namespace BEAR\Package\Injector;

use BEAR\AppMeta\Meta;
use BEAR\Package\Exception\InvalidWriteDirException;
use BEAR\Package\Types;

use function preg_match;
use function rtrim;
use function str_replace;
use function str_starts_with;

/**
 * @psalm-import-type AppName from Types
 * @psalm-import-type AppDir from Types
 * @psalm-import-type Context from Types
 * @psalm-import-type WriteDir from Types
 */
final class AppDirs
{
    /** @codeCoverageIgnore */
    private function __construct()
    {
    }

    /**
     * Meta writing under {writeDir}/{Vendor}/{Project}/{context}.
     *
     * @param AppName       $appName
     * @param Context       $context
     * @param AppDir        $appDir
     * @param WriteDir|null $writeDir absolute path; defaults to {appDir}/var
     *
     * @throws InvalidWriteDirException
     */
    public static function meta(string $appName, string $context, string $appDir, string|null $writeDir = null): Meta
    {
        if ($writeDir === null) {
            return new Meta($appName, $context, $appDir);
        }

        if (! self::isAbsolute($writeDir)) {
            throw new InvalidWriteDirException($writeDir);
        }

        $base = rtrim($writeDir, '/\\') . '/' . str_replace('\\', '/', $appName) . '/' . $context;

        return new Meta($appName, $context, $appDir, $base . '/tmp', $base . '/log');
    }

    private static function isAbsolute(string $path): bool
    {
        return str_starts_with($path, '/')
            || str_starts_with($path, '\\\\')
            || (bool) preg_match('#^[A-Za-z]:[/\\\\]#', $path);
    }

    /**
     * Compiled DI scripts, never under writeDir: the deployment artifact carries them.
     *
     * @param AppDir  $appDir
     * @param Context $context
     *
     * @return non-empty-string
     */
    public static function script(string $appDir, string $context): string
    {
        return $appDir . '/var/tmp/' . $context . '/di';
    }
}
