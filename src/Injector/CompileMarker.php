<?php

declare(strict_types=1);

namespace BEAR\Package\Injector;

use BEAR\Package\Exception\DirectoryNotWritableException;
use BEAR\Package\Types;

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
 * Presence means a compile has run here, not that the scripts still match the sources.
 *
 * @see https://github.com/bearsunday/BEAR.Package/issues/483
 * @psalm-import-type AppName from Types
 * @psalm-import-type Context from Types
 * @psalm-import-type TmpDir from Types
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

        /** @var mixed $time */
        $time = $record['time'] ?? null;

        return self::record(
            self::field($record, 'app'),
            self::field($record, 'context'),
            self::field($record, 'tmpDir'),
            is_int($time) ? $time : 0,
        );
    }

    /**
     * @param array<array-key, mixed> $record
     *
     * @return non-empty-string|null
     */
    private static function field(array $record, string $key): string|null
    {
        /** @var mixed $value */
        $value = $record[$key] ?? null;

        return is_string($value) && $value !== '' ? $value : null;
    }

    /**
     * @param AppName|null $appName
     * @param Context|null $context
     * @param TmpDir|null  $tmpDir
     */
    private static function record(string|null $appName, string|null $context, string|null $tmpDir, int $time): CompileRecord|null
    {
        if ($appName === null || $context === null || $tmpDir === null) {
            return null;
        }

        return new CompileRecord($appName, $context, $tmpDir, $time);
    }

    /**
     * Whether the scripts here are this application's build of this context.
     *
     * Not where they write. The recorded tmpDir is what the build's own container answers,
     * which is a fact about the build and travels with it - comparing it to the boot's was
     * what stopped an archive starting anywhere but the machine that packed it.
     *
     * @param AppName $appName
     * @param Context $context
     */
    public static function matches(string $scriptDir, string $appName, string $context): bool
    {
        $record = self::read($scriptDir);

        return $record !== null && $record->appName === $appName && $record->context === $context;
    }

    /**
     * Written through a temporary file: a concurrent boot must not read a half-written marker.
     *
     * Takes plain strings - a Meta hands them over as such - and read() is where a record
     * has to be valid to exist.
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
