<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use BEAR\Package\Exception\PharImportOutsideTreeException;
use BEAR\Package\Exception\PharImportWithoutWriteDirException;
use BEAR\Package\Exception\PharNotCompiledException;
use BEAR\Package\Exception\PharSymlinkedDirectoryException;
use BEAR\Package\Exception\PharWriteDirMismatchException;
use BEAR\Package\Exception\PharWritesInsideArchiveException;
use BEAR\Package\Injector\AppDirs;
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

use function assert;
use function in_array;
use function realpath;
use function rtrim;
use function str_ends_with;
use function str_replace;
use function str_starts_with;
use function strlen;
use function substr;

/**
 * What goes into an archive, and whether the tree can become one at all.
 *
 * @psalm-import-type AppDir from Types
 * @psalm-import-type Context from Types
 * @psalm-import-type ScriptDir from Types
 * @psalm-import-type WriteDir from Types
 * @psalm-import-type PharPath from Types
 */
final class PharManifest
{
    /** Ray.Compiler build noise: written next to the scripts, read by no boot. */
    private const SCRIPT_DIR_NOISE = ['compile.lock', '_bindings.log', 'bindings.md'];

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
     * @return non-empty-array<AppDir, ScriptDir>
     *
     * @throws PharNotCompiledException
     * @throws PharWritesInsideArchiveException
     * @throws PharImportWithoutWriteDirException
     * @throws PharWriteDirMismatchException
     * @throws PharImportOutsideTreeException
     */
    public static function roots(string $appDir, string $context, array $imports): array
    {
        // Both spellings: a marker holds text, and var/ or current/ may be a symlink.
        $archiveDir = self::resolve($appDir);
        $bases = [self::normalize($appDir), $archiveDir];

        $roots = [$archiveDir => AppDirs::script($archiveDir, $context)];
        self::writesOutside($bases, $archiveDir, $context);
        foreach ($imports as $import) {
            $importDir = self::resolve($import->appDir());
            if (! self::isUnder($importDir, $archiveDir)) {
                throw new PharImportOutsideTreeException($importDir, $archiveDir);
            }

            // Before the marker: an import with no write directory has nowhere outside to write,
            // whatever its scripts happen to hold, and that is the answer the operator needs.
            if ($import->writeDir === null) {
                throw new PharImportWithoutWriteDirException($importDir);
            }

            $record = self::writesOutside($bases, $importDir, $import->context);
            $declared = self::normalize(AppDirs::tmpDirIn($import->appName, $import->context, $import->writeDir));
            if (self::normalize($record->tmpDir) !== $declared) {
                throw new PharWriteDirMismatchException($importDir, $record->tmpDir, $declared);
            }

            $roots[$importDir] = AppDirs::script($importDir, $import->context);
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
        $scriptDir = AppDirs::script($appDir, $context);
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
     * The writeDir a tmpDir was derived from, or null when it was not: only
     * {writeDir}/{Vendor}/{Project}/{context}/tmp names one.
     *
     * @return WriteDir|null in the marker's own spelling - an operator runs it
     */
    public static function writeDirOf(CompileRecord $record): string|null
    {
        $suffix = '/' . str_replace('\\', '/', $record->appName) . '/' . $record->context . '/tmp';
        if (! str_ends_with(self::normalize($record->tmpDir), $suffix)) {
            return null;
        }

        // normalize() never changes the length, so the suffix cuts at the same place in both.
        $writeDir = substr($record->tmpDir, 0, -strlen($suffix));

        return $writeDir === '' ? null : $writeDir;
    }

    /**
     * Nothing directly under the application root ships, of each var/ only the DI scripts.
     *
     * $appDir must be resolved, as roots() returns it: a raw path lets every var/ path ship.
     *
     * @param AppDir                             $appDir resolved application root
     * @param non-empty-array<AppDir, ScriptDir> $roots  app root => script dir
     * @param PharPath                           $output
     *
     * @return Iterator<SplFileInfo>
     */
    public static function files(string $appDir, array $roots, string $output): Iterator
    {
        $base = self::normalize($appDir);
        $excluded = [self::normalize($output), $base . '/tests'];
        $filter = static function (mixed $file) use ($roots, $excluded, $base): bool {
            assert($file instanceof SplFileInfo);
            $name = $file->getFilename();
            // Deeper in the tree: an imported application and a --prefer-source vendor bring their own.
            if ($name === '.git' || str_starts_with($name, '.env')) {
                return false;
            }

            if ($file->isDir() && $file->isLink()) {
                throw new PharSymlinkedDirectoryException($file->getPathname());
            }

            if (self::normalize($file->getPath()) === $base && (! $file->isDir() || str_starts_with($name, '.'))) {
                return false;
            }

            $path = self::normalize($file->getPathname());
            if (in_array($path, $excluded, true)) {
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
     * Whether a path under some application's var/ ships; null when it is under none.
     *
     * @param non-empty-array<AppDir, ScriptDir> $roots
     */
    private static function shipsFromVar(string $path, string $name, array $roots): bool|null
    {
        foreach ($roots as $root => $scriptDir) {
            if (! self::isUnder($path, $root . '/var')) {
                continue;
            }

            if (self::isUnder($path, $scriptDir)) {
                return ! in_array($name, self::SCRIPT_DIR_NOISE, true);
            }

            // Ancestors of the script directory must pass, or the iterator never descends to it.
            return self::isUnder($scriptDir, $path);
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
