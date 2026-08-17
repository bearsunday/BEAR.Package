<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use BEAR\Package\Exception\PharImportOutsideTreeException;
use BEAR\Package\Exception\PharNotCompiledException;
use BEAR\Package\Exception\PharSymlinkedDirectoryException;
use BEAR\Package\Exception\PharWriteDirMismatchException;
use BEAR\Package\Exception\PharWritesInsideArchiveException;
use BEAR\Package\Injector\CompiledScripts;
use BEAR\Package\Injector\CompileMarker;
use BEAR\Package\Injector\CompileRecord;
use BEAR\Package\Module\Import\ImportApp;
use BEAR\Package\Types;
use FilesystemIterator;
use Iterator;
use RecursiveCallbackFilterIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

use function array_keys;
use function assert;
use function in_array;
use function realpath;
use function rtrim;
use function sort;
use function str_replace;
use function str_starts_with;

/**
 * What goes into an archive, and whether the tree can become one at all.
 *
 * @psalm-import-type AppDir from Types
 * @psalm-import-type Context from Types
 * @psalm-import-type BuildDir from Types
 * @psalm-import-type WriteDir from Types
 * @psalm-import-type PharPath from Types
 */
final class PharManifest
{
    /** The only top-level directories an archive carries. */
    private const SHIPPED_DIRS = ['src', 'public', 'bin', 'vendor', 'var'];

    /** Ray.Compiler noise: written beside what it produced, read by no boot. */
    private const BUILD_NOISE = ['compile.lock', '_bindings.log', 'bindings.md'];

    /** What a run writes. */
    private const UNSHIPPED_VAR_DIRS = ['log', 'tmp'];

    /** @codeCoverageIgnore */
    private function __construct()
    {
    }

    /**
     * All returned paths use forward slashes, whatever the platform spells them with.
     *
     * @param AppDir          $appDir  resolved application root
     * @param Context         $context
     * @param list<ImportApp> $imports as declared by the compiled container
     *
     * @return non-empty-array<AppDir, BuildDir>
     *
     * @throws PharNotCompiledException
     * @throws PharWritesInsideArchiveException
     * @throws PharWriteDirMismatchException
     * @throws PharImportOutsideTreeException
     */
    public static function roots(string $appDir, string $context, array $imports): array
    {
        // Both spellings: a marker holds text, and var/ or current/ may be a symlink.
        $archiveDir = self::resolve($appDir);
        $bases = [self::normalize($appDir), $archiveDir];

        $roots = [$archiveDir => CompiledScripts::buildDir($archiveDir, $context)];
        $host = self::writesOutside($bases, $archiveDir, $context);
        $writeDir = $host->writeDir;
        foreach ($imports as $import) {
            $importDir = self::resolve($import->appDir());
            if (! self::isUnder($importDir, $archiveDir)) {
                throw new PharImportOutsideTreeException($importDir, $archiveDir);
            }

            $record = self::writesOutside($bases, $importDir, $import->context);
            // Beside the host, because that is the directory the container hands it at boot.
            if ($writeDir !== null && ! self::isUnder($record->tmpDir, $writeDir)) {
                throw new PharWriteDirMismatchException($importDir, $record->tmpDir, $writeDir);
            }

            $roots[$importDir] = CompiledScripts::buildDir($importDir, $import->context);
        }

        return $roots;
    }

    /**
     * @param non-empty-string $dir
     *
     * @return non-empty-string resolved when possible, always forward-slashed
     */
    private static function resolve(string $dir): string
    {
        $real = realpath($dir);
        if ($real === false) {
            return self::normalize($dir);
        }

        /** @var non-empty-string $real psalm's realpath stub says string; phpstan's already says non-empty */
        return self::normalize($real);
    }

    /**
     * @param non-empty-list<AppDir> $archiveBases raw and resolved forms of the tree root
     * @param AppDir                 $appDir       the application to check
     * @param Context                $context
     *
     * @return CompileRecord what the marker says the scripts were compiled for
     *
     * @throws PharNotCompiledException
     * @throws PharWritesInsideArchiveException
     */
    private static function writesOutside(array $archiveBases, string $appDir, string $context): CompileRecord
    {
        $scriptDir = CompiledScripts::dir($appDir, $context);
        $record = CompileMarker::read($scriptDir);
        if ($record === null) {
            throw new PharNotCompiledException($scriptDir);
        }

        $tmpDirs = [self::normalize($record->tmpDir), self::resolve($record->tmpDir)];
        foreach ($archiveBases as $base) {
            foreach ($tmpDirs as $tmpDir) {
                if (self::isUnder($tmpDir, $base)) {
                    throw new PharWritesInsideArchiveException($appDir, $record->tmpDir);
                }
            }
        }

        return $record;
    }

    /**
     * Of the application root only SHIPPED_DIRS, and of each var/ only this build.
     *
     * $appDir must be resolved, as roots() returns it: a raw path lets every var/ path ship.
     *
     * @param AppDir                            $appDir resolved application root
     * @param non-empty-array<AppDir, BuildDir> $roots  app root => build dir
     * @param PharPath                          $output an override can name a shipped directory
     *
     * @return Iterator<SplFileInfo>
     */
    public static function files(string $appDir, array $roots, string $output): Iterator
    {
        $base = self::normalize($appDir);
        $excludedOutput = self::normalize($output);
        $filter = static function (mixed $file) use ($roots, $excludedOutput, $base): bool {
            assert($file instanceof SplFileInfo);
            $name = $file->getFilename();
            // Deeper in the tree: an imported application and a --prefer-source vendor bring their own.
            if ($name === '.git' || str_starts_with($name, '.env')) {
                return false;
            }

            if ($file->isDir() && $file->isLink()) {
                throw new PharSymlinkedDirectoryException($file->getPathname());
            }

            if (self::normalize($file->getPath()) === $base) {
                return $file->isDir() && self::shipsFromRoot(self::normalize($file->getPathname()), $name, $roots);
            }

            $path = self::normalize($file->getPathname());
            if ($path === $excludedOutput) {
                return false;
            }

            return self::shipsFromVar($path, $name, $roots) ?? true;
        };

        /** @psalm-suppress MixedArgumentTypeCoercion, PossiblyInvalidArgument */
        return new RecursiveIteratorIterator(
            new RecursiveCallbackFilterIterator(new RecursiveDirectoryIterator($appDir, FilesystemIterator::SKIP_DOTS), $filter),
        );
    }

    /**
     * @param AppDir                            $appDir resolved application root
     * @param non-empty-array<AppDir, BuildDir> $roots
     *
     * @return list<string> sorted, as spelled on disk
     */
    public static function notPacked(string $appDir, array $roots): array
    {
        $left = [];
        foreach (new FilesystemIterator(self::normalize($appDir), FilesystemIterator::SKIP_DOTS) as $entry) {
            assert($entry instanceof SplFileInfo);
            $name = $entry->getFilename();
            if (! $entry->isDir() || str_starts_with($name, '.')) {
                continue;
            }

            if (self::shipsFromRoot(self::normalize($entry->getPathname()), $name, $roots)) {
                continue;
            }

            $left[] = $name;
        }

        sort($left);

        return $left;
    }

    /**
     * An imported application sits wherever the tree put it, and the host boots it from there.
     *
     * @param non-empty-array<AppDir, BuildDir> $roots
     */
    private static function shipsFromRoot(string $path, string $name, array $roots): bool
    {
        if (in_array($name, self::SHIPPED_DIRS, true)) {
            return true;
        }

        foreach (array_keys($roots) as $root) {
            if (self::isUnder($root, $path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Whether a path under some application's var/ ships; null when it is under none.
     *
     * @param non-empty-array<AppDir, BuildDir> $roots
     */
    private static function shipsFromVar(string $path, string $name, array $roots): bool|null
    {
        foreach ($roots as $root => $buildDir) {
            if (! self::isUnder($path, $root . '/var')) {
                continue;
            }

            if (self::isUnder($path, $buildDir)) {
                return ! in_array($name, self::BUILD_NOISE, true);
            }

            // var/ and var/build: the iterator only reaches the build directory through them.
            if (self::isUnder($buildDir, $path)) {
                return true;
            }

            foreach (self::UNSHIPPED_VAR_DIRS as $dir) {
                if (self::isUnder($path, $root . '/var/' . $dir)) {
                    return false;
                }
            }

            // Anything else under var/build: another context's build, or what an earlier release
            // left there.
            return ! self::isUnder($path, CompiledScripts::buildRoot($root));
        }

        return null;
    }

    /** Whether $path is $base or lies inside it, whichever way the platform spells them. */
    private static function isUnder(string $path, string $base): bool
    {
        $path = self::normalize($path);
        $base = rtrim(self::normalize($base), '/');

        return $path === $base || str_starts_with($path, $base . '/');
    }

    /**
     * @param T $path
     *
     * @return (T is non-empty-string ? non-empty-string : string)
     *
     * @template T of string
     */
    private static function normalize(string $path): string
    {
        return str_replace('\\', '/', $path);
    }
}
