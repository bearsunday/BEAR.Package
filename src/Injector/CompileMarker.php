<?php

declare(strict_types=1);

namespace BEAR\Package\Injector;

use BEAR\Package\Exception\DirectoryNotWritableException;

use function explode;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function rename;
use function sprintf;
use function time;
use function uniqid;
use function unlink;

/**
 * Marker file written after a successful AOT compile.
 *
 * Presence means "a compile has run for this script directory", not that the scripts still
 * match the source tree: freshness is the deploy's responsibility. It records the writable
 * directory the compile used, since the scripts hold it and the script directory does not.
 *
 * @see https://github.com/bearsunday/BEAR.Package/issues/483
 */
final class CompileMarker
{
    private const MARKER = '.compile-marker';

    /** @codeCoverageIgnore */
    private function __construct()
    {
    }

    public static function path(string $scriptDir): string
    {
        return $scriptDir . '/' . self::MARKER;
    }

    /** Scripts here were compiled for $tmpDir, the writable directory their bindings hold. */
    public static function matches(string $scriptDir, string $tmpDir): bool
    {
        $path = self::path($scriptDir);

        return file_exists($path) && explode("\n", (string) @file_get_contents($path), 2)[0] === $tmpDir;
    }

    /**
     * Written through a temporary file: a concurrent boot must not read a half-written marker.
     *
     * @throws DirectoryNotWritableException A marker that cannot be persisted makes every later boot recompile.
     */
    public static function write(string $scriptDir, string $tmpDir): void
    {
        $path = self::path($scriptDir);
        $temp = $path . '.' . uniqid('', true);
        $content = sprintf("%s\ncompiled at %d\n", $tmpDir, time());
        if (@file_put_contents($temp, $content) !== false && @rename($temp, $path)) {
            return;
        }

        @unlink($temp);

        throw new DirectoryNotWritableException($path);
    }
}
