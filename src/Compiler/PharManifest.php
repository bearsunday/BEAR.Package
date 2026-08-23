<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use BEAR\Package\Exception\PharImportOutsideTreeException;
use BEAR\Package\Exception\PharNotCompiledException;
use BEAR\Package\Exception\PharSymlinkedDirectoryException;
use BEAR\Package\Exception\PharWritesInsideArchiveException;
use BEAR\Package\Injector\CompileMarker;
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
use function dirname;
use function explode;
use function in_array;
use function realpath;
use function rtrim;
use function sort;
use function str_replace;
use function str_starts_with;
use function strlen;
use function substr;

/**
 * What goes into an archive, and whether the tree can become one at all.
 *
 * @psalm-import-type AppDir from Types
 * @psalm-import-type Context from Types
 * @psalm-import-type BuildDir from Types
 * @psalm-import-type WriteDir from Types
 * @psalm-import-type PharPath from Types
 * @psalm-import-type StubEntry from Types
 */
final class PharManifest
{
    /** The only top-level directories an archive carries. */
    private const SHIPPED_DIRS = ['src', 'public', 'bin', 'vendor', 'var'];

    /**
     * The one root file that is the artifact's, not the project's.
     *
     * Its requires are written relative to the directory it sits in, so they resolve inside
     * the archive and nowhere else: `opcache.preload` has to name it there.
     */
    private const SHIPPED_ROOT_FILES = ['preload.php'];

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
     * @param AppDir          $appDir   resolved application root
     * @param BuildDir        $buildDir the host's, as the compile wrote it
     * @param list<ImportApp> $imports  as declared by the compiled container
     *
     * @return non-empty-array<AppDir, BuildDir>
     *
     * @throws PharNotCompiledException
     * @throws PharWritesInsideArchiveException
     * @throws PharImportOutsideTreeException
     */
    public static function roots(string $appDir, string $buildDir, array $imports): array
    {
        // Both spellings: a marker holds text, and var/ or current/ may be a symlink.
        $archiveDir = self::resolve($appDir);
        $bases = [self::normalize($appDir), $archiveDir];

        $roots = [$archiveDir => self::resolve($buildDir)];
        self::writesOutside($bases, $archiveDir, $buildDir);
        foreach ($imports as $import) {
            $importDir = self::resolve($import->appDir());
            if (! self::isUnder($importDir, $archiveDir)) {
                throw new PharImportOutsideTreeException($importDir, $archiveDir);
            }

            self::writesOutside($bases, $importDir, $import->buildDir());
            $roots[$importDir] = self::resolve($import->buildDir());
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
     * @param BuildDir               $buildDir     where its compile wrote
     *
     * @throws PharNotCompiledException
     * @throws PharWritesInsideArchiveException
     */
    private static function writesOutside(array $archiveBases, string $appDir, string $buildDir): void
    {
        $scriptDir = $buildDir . '/di';
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
    }

    /**
     * Of the application root only SHIPPED_DIRS, and of each var/ only this build.
     *
     * $appDir must be resolved, as roots() returns it: a raw path lets every var/ path ship.
     *
     * @param AppDir                            $appDir resolved application root
     * @param non-empty-array<AppDir, BuildDir> $roots  app root => build dir
     * @param PharPath                          $output an override can name a shipped directory
     * @param StubEntry                         $entry  relative to appDir
     *
     * @return Iterator<SplFileInfo>
     */
    public static function files(string $appDir, array $roots, string $output, string $entry): Iterator
    {
        $base = self::normalize($appDir);
        $excludedOutput = self::normalize($output);
        $entryDir = self::entryDir($entry);
        $filter = static function (mixed $file) use ($roots, $excludedOutput, $base, $entryDir): bool {
            assert($file instanceof SplFileInfo);
            $name = $file->getFilename();
            // Deeper in the tree: an imported application and a --prefer-source vendor bring their own.
            if ($name === '.git' || str_starts_with($name, '.env')) {
                return false;
            }

            if ($file->isDir() && $file->isLink()) {
                throw new PharSymlinkedDirectoryException($file->getPathname());
            }

            $path = self::normalize($file->getPathname());
            if ($path === $excludedOutput) {
                return false;
            }

            if (self::normalize($file->getPath()) === $base && ! $file->isDir()) {
                return in_array($name, self::SHIPPED_ROOT_FILES, true);
            }

            if (! self::shipsFromRoot($path, $roots, $base, $entryDir)) {
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
     * @param StubEntry                         $entry  relative to appDir
     *
     * @return list<string> sorted, as spelled on disk
     */
    public static function notPacked(string $appDir, array $roots, string $entry): array
    {
        $entryDir = self::entryDir($entry);
        $left = [];
        foreach (new FilesystemIterator(self::normalize($appDir), FilesystemIterator::SKIP_DOTS) as $file) {
            assert($file instanceof SplFileInfo);
            $name = $file->getFilename();
            if (! $file->isDir() || str_starts_with($name, '.')) {
                continue;
            }

            if (self::shipsFromRoot(self::normalize($file->getPathname()), $roots, self::normalize($appDir), $entryDir)) {
                continue;
            }

            $left[] = $name;
        }

        sort($left);

        return $left;
    }

    /** @param StubEntry $entry */
    private static function entryDir(string $entry): string
    {
        return explode('/', self::normalize($entry), 2)[0];
    }

    /**
     * Whether the top-level directory $path lies in is one the archive carries.
     *
     * The stub requires $entry, so whatever holds it ships even when nothing else there does.
     *
     * @param non-empty-array<AppDir, BuildDir> $roots
     */
    private static function shipsFromRoot(string $path, array $roots, string $base, string $entryDir): bool
    {
        $top = self::topDir($path, $base);

        return $top === $entryDir || in_array($top, self::SHIPPED_DIRS, true) || self::holdsImport($path, $roots, $base);
    }

    /**
     * Whether $path is inside an imported application, or on the chain that reaches one.
     *
     * Both directions: an ancestor has to pass for the iterator to descend, and nothing parked
     * beside the application may ride along with it.
     *
     * @param non-empty-array<AppDir, BuildDir> $roots
     */
    private static function holdsImport(string $path, array $roots, string $base): bool
    {
        foreach (array_keys($roots) as $root) {
            if ($root !== $base && (self::isUnder($root, $path) || self::isUnder($path, $root))) {
                return true;
            }
        }

        return false;
    }

    /** @return string the first segment of $path below $base, or the empty string at the root */
    private static function topDir(string $path, string $base): string
    {
        return explode('/', substr($path, strlen($base) + 1), 2)[0];
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
            return ! self::isUnder($path, dirname($buildDir));
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
