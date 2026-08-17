<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use BEAR\Package\Exception\PharComposerUnreadableException;
use BEAR\Package\Types;
use JsonException;

use function array_keys;
use function explode;
use function file_get_contents;
use function is_array;
use function is_string;
use function json_decode;
use function str_replace;
use function str_starts_with;
use function substr;

use const JSON_THROW_ON_ERROR;

/**
 * The top-level directories an application's composer.json makes autoloadable.
 *
 * A classmap over a directory that replaces a vendor class puts that directory on the boot
 * path, so an archive without it cannot start.
 *
 * @psalm-import-type AppDir from Types
 */
final class AutoloadDirs
{
    /**
     * Every autoload key that names paths.
     *
     * exclude-from-classmap included: a path can only be excluded from a tree that is scanned,
     * so it names the same autoload surface as the keys that carry it.
     */
    private const KEYS = ['psr-4', 'psr-0', 'classmap', 'files', 'exclude-from-classmap'];

    /** @codeCoverageIgnore */
    private function __construct()
    {
    }

    /**
     * @param AppDir $appDir
     *
     * @return list<string> in declaration order, without duplicates
     *
     * @throws PharComposerUnreadableException
     */
    public static function of(string $appDir): array
    {
        $file = $appDir . '/composer.json';
        $autoload = self::autoload($file);
        $dirs = [];
        foreach (self::KEYS as $key) {
            foreach (self::paths($autoload[$key] ?? null, $file) as $path) {
                $top = self::topDir($path);
                // The tree root itself, or a path leaving it: no directory of the tree to name.
                if ($top === '' || $top === '.' || $top === '..') {
                    continue;
                }

                $dirs[$top] = true;
            }
        }

        return array_keys($dirs);
    }

    /**
     * @return array<array-key, mixed>
     *
     * @throws PharComposerUnreadableException
     */
    private static function autoload(string $file): array
    {
        $json = @file_get_contents($file);
        if ($json === false) {
            throw new PharComposerUnreadableException($file);
        }

        try {
            /** @var mixed $composer */
            $composer = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new PharComposerUnreadableException($file);
        }

        if (! is_array($composer)) {
            throw new PharComposerUnreadableException($file);
        }

        /** @var mixed $autoload */
        $autoload = $composer['autoload'] ?? [];
        if (! is_array($autoload)) {
            throw new PharComposerUnreadableException($file);
        }

        return $autoload;
    }

    /**
     * A path, a list of them, or a namespace map holding either.
     *
     * @return list<string>
     *
     * @throws PharComposerUnreadableException
     */
    private static function paths(mixed $value, string $file): array
    {
        if ($value === null) {
            return [];
        }

        if (is_string($value)) {
            return [$value];
        }

        if (! is_array($value)) {
            throw new PharComposerUnreadableException($file);
        }

        $paths = [];
        /** @var mixed $item */
        foreach ($value as $item) {
            foreach (self::paths($item, $file) as $path) {
                $paths[] = $path;
            }
        }

        return $paths;
    }

    /** @return string the first segment of $path, however the manifest spells the separator */
    private static function topDir(string $path): string
    {
        $path = str_replace('\\', '/', $path);
        // "./src" and "src" name one directory.
        if (str_starts_with($path, './')) {
            $path = substr($path, 2);
        }

        return explode('/', $path, 2)[0];
    }
}
