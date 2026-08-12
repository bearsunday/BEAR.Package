<?php

declare(strict_types=1);

namespace BEAR\Package\Injector;

use BEAR\Package\Exception\DirectoryNotWritableException;

use function file_exists;
use function file_put_contents;
use function sprintf;
use function time;

/**
 * Marker file written after a successful AOT compile.
 *
 * Presence means "a compile has run for this script directory"; freshness is
 * the deploy's responsibility — the framework does not verify the scripts
 * still match the source tree. On a missing marker, {@see PackageInjector}
 * compiles on demand and emits `E_USER_WARNING`.
 *
 * @see https://github.com/bearsunday/BEAR.Package/issues/483
 * @see https://bearsunday.github.io/manuals/1.0/en/production.html#compilation-recommended
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

    public static function exists(string $scriptDir): bool
    {
        return file_exists(self::path($scriptDir));
    }

    /** @throws DirectoryNotWritableException A marker that cannot be persisted makes every later boot recompile. */
    public static function write(string $scriptDir): void
    {
        $path = self::path($scriptDir);
        if (@file_put_contents($path, sprintf('compiled at %d', time())) !== false) {
            return;
        }

        throw new DirectoryNotWritableException($path);
    }
}
