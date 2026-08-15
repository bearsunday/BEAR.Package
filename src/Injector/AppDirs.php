<?php

declare(strict_types=1);

namespace BEAR\Package\Injector;

use BEAR\AppMeta\Meta;
use BEAR\Package\Exception\InvalidWriteDirException;
use BEAR\Package\Exception\WriteDirRequiredException;
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
     * @throws WriteDirRequiredException
     */
    public static function meta(string $appName, string $context, string $appDir, string|null $writeDir = null): Meta
    {
        if ($writeDir === null && self::isStreamUri($appDir)) {
            throw new WriteDirRequiredException($appDir);
        }

        if ($writeDir === null) {
            return new Meta($appName, $context, $appDir);
        }

        $base = self::base($appName, $context, $writeDir);

        return new Meta($appName, $context, $appDir, $base . '/tmp', $base . '/log');
    }

    /**
     * The tmp directory a Meta built from these values would hold - computed, not created,
     * so the pack can compare a marker against a declaration without making directories.
     *
     * @param AppName       $appName
     * @param Context       $context
     * @param AppDir        $appDir
     * @param WriteDir|null $writeDir
     *
     * @return non-empty-string
     *
     * @throws InvalidWriteDirException
     */
    public static function tmpDirFor(string $appName, string $context, string $appDir, string|null $writeDir): string
    {
        if ($writeDir === null) {
            return $appDir . '/var/tmp/' . $context;
        }

        return self::base($appName, $context, $writeDir) . '/tmp';
    }

    /**
     * @param AppName  $appName
     * @param Context  $context
     * @param WriteDir $writeDir
     *
     * @return non-empty-string
     *
     * @throws InvalidWriteDirException
     */
    private static function base(string $appName, string $context, string $writeDir): string
    {
        if (! self::isAbsolute($writeDir)) {
            throw new InvalidWriteDirException($writeDir);
        }

        return rtrim($writeDir, '/\\') . '/' . str_replace('\\', '/', $appName) . '/' . $context;
    }

    private static function isAbsolute(string $path): bool
    {
        return str_starts_with($path, '/')
            || str_starts_with($path, '\\\\')
            || (bool) preg_match('#^[A-Za-z]:[/\\\\]#', $path);
    }

    private static function isStreamUri(string $path): bool
    {
        return (bool) preg_match('#^[A-Za-z][A-Za-z0-9+.\-]*://#', $path);
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
