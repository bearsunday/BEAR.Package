<?php

declare(strict_types=1);

namespace BEAR\Package\Injector;

use BEAR\Package\Exception\DirectoryNotWritableException;

use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function is_array;
use function is_int;
use function is_string;
use function json_decode;
use function json_encode;
use function rename;
use function time;
use function uniqid;
use function unlink;

use const JSON_THROW_ON_ERROR;

/**
 * Marker file written after a successful AOT compile.
 *
 * Presence means "a compile has run for this script directory", not that the scripts still
 * match the source tree: freshness is the deploy's responsibility. It records what the
 * scripts cannot say about themselves - the application, the context, and the writable
 * directory their bindings hold - so a boot can tell whether they are the ones it needs,
 * and a deployment tool can read a script directory without guessing from its path.
 *
 * @see https://github.com/bearsunday/BEAR.Package/issues/483
 */
final class CompileMarker
{
    public const FILENAME = '.bear-compile.json';

    /** @codeCoverageIgnore */
    private function __construct()
    {
    }

    public static function path(string $scriptDir): string
    {
        return $scriptDir . '/' . self::FILENAME;
    }

    /** Null when the directory holds no marker, or one this version cannot read. */
    public static function read(string $scriptDir): CompileRecord|null
    {
        $path = self::path($scriptDir);
        if (! file_exists($path)) {
            return null;
        }

        /** @var mixed $record */
        $record = json_decode((string) @file_get_contents($path), true);
        if (! is_array($record)) {
            return null;
        }

        /** @var mixed $appName */
        $appName = $record['app'] ?? null;
        /** @var mixed $context */
        $context = $record['context'] ?? null;
        /** @var mixed $tmpDir */
        $tmpDir = $record['tmpDir'] ?? null;
        /** @var mixed $time */
        $time = $record['time'] ?? null;
        if (! is_string($appName) || ! is_string($context) || ! is_string($tmpDir) || $appName === '' || $context === '' || $tmpDir === '') {
            return null;
        }

        return new CompileRecord($appName, $context, $tmpDir, is_int($time) ? $time : 0);
    }

    /** Scripts here were compiled for $tmpDir, the writable directory their bindings hold. */
    public static function matches(string $scriptDir, string $tmpDir): bool
    {
        return self::read($scriptDir)?->tmpDir === $tmpDir;
    }

    /**
     * Written through a temporary file: a concurrent boot must not read a half-written marker.
     *
     * @param non-empty-string $appName
     * @param non-empty-string $context
     * @param non-empty-string $tmpDir
     *
     * @throws DirectoryNotWritableException A marker that cannot be persisted makes every later boot recompile.
     */
    public static function write(string $scriptDir, string $appName, string $context, string $tmpDir): void
    {
        $path = self::path($scriptDir);
        $temp = $path . '.' . uniqid('', true);
        $content = json_encode([
            'app' => $appName,
            'context' => $context,
            'tmpDir' => $tmpDir,
            'time' => time(),
        ], JSON_THROW_ON_ERROR) . "\n";
        if (@file_put_contents($temp, $content) !== false && @rename($temp, $path)) {
            return;
        }

        @unlink($temp);

        throw new DirectoryNotWritableException($path);
    }
}
