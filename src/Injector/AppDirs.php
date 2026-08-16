<?php

declare(strict_types=1);

namespace BEAR\Package\Injector;

use BEAR\AppMeta\Meta;
use BEAR\Package\Exception\InvalidWriteDirException;
use BEAR\Package\Exception\WriteDirRequiredException;
use BEAR\Package\Types;

use function dirname;
use function preg_match;
use function rtrim;
use function str_ends_with;
use function str_replace;
use function str_starts_with;
use function strlen;
use function substr;

/**
 * @psalm-import-type AppName from Types
 * @psalm-import-type AppDir from Types
 * @psalm-import-type Context from Types
 * @psalm-import-type WriteDir from Types
 * @psalm-import-type ScriptDir from Types
 * @psalm-import-type TmpDir from Types
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
     * The write directory a tmp directory was derived from, or null when it names none.
     *
     * The inverse of tmpDirIn(), so this class owns the layout in both directions: the runtime
     * reads it out of a Meta, the pack out of a compile marker.
     *
     * @param AppName $appName
     * @param TmpDir  $tmpDir
     *
     * @return WriteDir|null in the spelling it was given
     */
    public static function writeDirOf(string $appName, string $tmpDir): string|null
    {
        // {writeDir}/{Vendor}/{Project}/{context}/tmp - the context is one segment, whatever it is
        $suffix = '/' . str_replace('\\', '/', $appName);
        $base = dirname($tmpDir, 2);
        if (! str_ends_with(str_replace('\\', '/', $base), $suffix)) {
            return null;
        }

        $writeDir = substr($base, 0, -strlen($suffix));

        return $writeDir === '' ? null : $writeDir;
    }

    /**
     * The tmp directory meta() puts under a write directory - computed, not created, so the pack
     * can compare a marker against a declaration without making one.
     *
     * @param AppName  $appName
     * @param Context  $context
     * @param WriteDir $writeDir
     *
     * @return TmpDir
     *
     * @throws InvalidWriteDirException
     */
    public static function tmpDirIn(string $appName, string $context, string $writeDir): string
    {
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
     * @return ScriptDir
     */
    public static function script(string $appDir, string $context): string
    {
        return $appDir . '/var/tmp/' . $context . '/di';
    }
}
