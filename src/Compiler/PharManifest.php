<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use BEAR\Package\Exception\PharImportOutsideTreeException;
use BEAR\Package\Exception\PharNotCompiledException;
use BEAR\Package\Exception\PharSymlinkedDirectoryException;
use BEAR\Package\Exception\PharWriteDirMismatchException;
use BEAR\Package\Exception\PharWritesInsideArchiveException;
use BEAR\Package\Injector\AppDirs;
use BEAR\Package\Injector\CompileMarker;
use BEAR\Package\Injector\CompileRecord;
use BEAR\Package\Module\Import\ImportApp;
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
 * Applications are the host plus what its compiled container declares as imports: a tree
 * that merely looks like an application is never treated as one. Every application must
 * write outside the archive, and where its own declaration derives, or the boot would
 * recompile - in a read-only filesystem, or into a directory nothing reads.
 *
 * Override injector scripts (Injector::getOverrideInstance()) ship with the script
 * directory, but a phar boot is expected to use the default injector.
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
     * @param non-empty-string $appDir  resolved application root
     * @param non-empty-string $context
     * @param list<ImportApp>  $imports as declared by the compiled container
     *
     * @return non-empty-array<non-empty-string, non-empty-string> app root => script dir
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

        $roots = [$archiveDir => AppDirs::script($archiveDir, $context)];
        self::writesOutside($bases, $archiveDir, $context);
        foreach ($imports as $import) {
            $importDir = self::resolve($import->appDir());
            if (! self::isUnder($importDir, $archiveDir)) {
                throw new PharImportOutsideTreeException($importDir, $archiveDir);
            }

            $record = self::writesOutside($bases, $importDir, $import->context);
            $declared = self::normalize(AppDirs::tmpDirFor($import->appName, $import->context, $importDir, $import->writeDir));
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
     * @param non-empty-list<non-empty-string> $archiveBases raw and resolved forms of the tree root
     * @param non-empty-string                 $appDir       the application to check
     * @param non-empty-string                 $context
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
     * @return non-empty-string|null in the marker's own spelling - an operator runs it
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
     * No .env* file ships wherever it sits: a boot reads its values from the compiled
     * scripts, and an archive is distributable. autoload.php and preload.php hold
     * build-time absolute paths. A symlinked directory stops the build - buildFromIterator()
     * cannot pack one.
     *
     * $appDir must be resolved, as roots() returns it: a raw path compares unequal to those
     * keys and lets every var/ path ship.
     *
     * @param non-empty-string                                    $appDir resolved application root
     * @param non-empty-array<non-empty-string, non-empty-string> $roots  app root => script dir
     * @param non-empty-string                                    $output
     *
     * @return Iterator<SplFileInfo>
     */
    public static function files(string $appDir, array $roots, string $output): Iterator
    {
        $base = self::normalize($appDir);
        $excluded = [self::normalize($output), $base . '/tests', $base . '/autoload.php', $base . '/preload.php'];
        $filter = static function (mixed $file) use ($roots, $excluded): bool {
            assert($file instanceof SplFileInfo);
            $name = $file->getFilename();
            if ($name === '.git' || str_starts_with($name, '.env')) {
                return false;
            }

            if ($file->isDir() && $file->isLink()) {
                throw new PharSymlinkedDirectoryException($file->getPathname());
            }

            $path = self::normalize($file->getPathname());
            if (in_array($path, $excluded, true)) {
                return false;
            }

            return self::varVerdict($path, $name, $roots) ?? true;
        };

        /** @psalm-suppress MixedArgumentTypeCoercion, PossiblyInvalidArgument */
        return new RecursiveIteratorIterator(
            new RecursiveCallbackFilterIterator(new RecursiveDirectoryIterator($appDir, FilesystemIterator::SKIP_DOTS), $filter),
        );
    }

    /**
     * Whether a path inside some application's var/ ships. Null when it is in no var/.
     *
     * @param non-empty-array<non-empty-string, non-empty-string> $roots app root => script dir
     */
    private static function varVerdict(string $path, string $name, array $roots): bool|null
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
