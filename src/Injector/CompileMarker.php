<?php

declare(strict_types=1);

namespace BEAR\Package\Injector;

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
 * compiles on demand and emits `E_USER_NOTICE`.
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

    public static function exists(string $scriptDir): bool
    {
        return file_exists(self::path($scriptDir));
    }

    public static function write(string $scriptDir): void
    {
        @file_put_contents(self::path($scriptDir), sprintf('compiled at %d', time()));
    }
}
