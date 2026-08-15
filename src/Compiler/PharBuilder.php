<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use BEAR\Package\Exception\PharEntryNotFoundException;
use BEAR\Package\Exception\PharEntryNotPackedException;
use BEAR\Package\Exception\PharImportOutsideTreeException;
use BEAR\Package\Exception\PharImportsUnreadableException;
use BEAR\Package\Exception\PharNotCompiledException;
use BEAR\Package\Exception\PharReadOnlyException;
use BEAR\Package\Exception\PharStaleOutputException;
use BEAR\Package\Exception\PharSymlinkedDirectoryException;
use BEAR\Package\Exception\PharWriteDirMismatchException;
use BEAR\Package\Exception\PharWritesInsideArchiveException;
use BEAR\Package\Injector\AppDirs;
use BEAR\Package\Injector\CompileMarker;
use BEAR\Package\Injector\CompileRecord;
use BEAR\Package\Module\Import\ImportApp;
use FilesystemIterator;
use Iterator;
use Phar;
use RecursiveCallbackFilterIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

use function assert;
use function basename;
use function clearstatcache;
use function count;
use function dirname;
use function file_exists;
use function filesize;
use function in_array;
use function ini_get;
use function is_file;
use function mkdir;
use function realpath;
use function rtrim;
use function sprintf;
use function str_ends_with;
use function str_replace;
use function str_starts_with;
use function strlen;
use function substr;
use function unlink;
use function var_export;

/**
 * Pack a compiled application tree into one archive.
 *
 * What ships is the framework's knowledge, not configuration: the tree, vendor/, and the
 * DI scripts - compile markers included - of the application and of every application it
 * imports. Logs, caches, every .env file, build outputs and build noise stay out.
 *
 * Nothing is guessed and nothing is passed in twice. Each script directory carries a
 * marker saying which application, which context and which writable directory it was
 * compiled for, and the imported applications come from the compiled container's own
 * declaration - an unrelated application tree in the same repository is never consulted
 * as an application, though its files travel with the tree like any others.
 *
 * One rule decides whether a tree can be an archive at all: every application in it must
 * write outside it, and must write exactly where its declaration says. An application
 * compiled to write into its own tree would recompile inside the read-only archive; one
 * compiled for a directory its declaration does not name would recompile on first boot.
 *
 * Override injectors (Injector::getOverrideInstance()) are out of scope: their scripts
 * live in a hash subdirectory that ships with the script directory, but a phar boot is
 * expected to use the default injector.
 *
 * Runs in bin/phar-worker.php: phar.readonly is INI_SYSTEM, so only a process started
 * with -d phar.readonly=0 can write an archive.
 */
final class PharBuilder
{
    /** Ray.Compiler build noise: written next to the scripts, read by no boot. */
    private const SCRIPT_DIR_NOISE = ['compile.lock', '_bindings.log', 'bindings.md'];

    /**
     * @param non-empty-string      $context
     * @param non-empty-string      $appDir
     * @param non-empty-string      $entry   stub entry, relative to appDir
     * @param non-empty-string|null $output  archive path; default {appDir}/var/build/{context}.phar
     *
     * @throws PharReadOnlyException
     * @throws PharEntryNotFoundException
     * @throws PharNotCompiledException
     * @throws PharImportsUnreadableException
     * @throws PharWritesInsideArchiveException
     * @throws PharWriteDirMismatchException
     * @throws PharImportOutsideTreeException
     * @throws PharStaleOutputException
     * @throws PharEntryNotPackedException
     */
    public function __invoke(string $context, string $appDir, string $entry, string|null $output = null): PharReport
    {
        if (ini_get('phar.readonly') === '1') {
            throw new PharReadOnlyException();
        }

        // Native form for the filesystem and Phar APIs; comparisons normalize separators.
        $appDirReal = realpath($appDir);
        assert($appDirReal !== false);
        /** @var non-empty-string $appDirReal psalm's realpath stub says string; phpstan's already says non-empty */
        if (! is_file($appDirReal . '/' . $entry)) {
            throw new PharEntryNotFoundException($appDirReal . '/' . $entry);
        }

        // The host marker is read before the container is asked for its imports, so an
        // uncompiled tree reports "not compiled", not a script-directory error.
        $hostDir = AppDirs::script($appDirReal, $context);
        $hostRecord = CompileMarker::read($hostDir);
        if ($hostRecord === null) {
            throw new PharNotCompiledException($hostDir);
        }

        $roots = self::roots($appDirReal, $context, ImportedApps::of($hostDir));
        $output ??= $appDirReal . '/var/build/' . $context . '.phar';
        @mkdir(dirname($output), 0777, true);
        @unlink($output);
        clearstatcache(true, $output);
        // new Phar() on a surviving file adds to it, and the stale entries would ship unnoticed.
        if (file_exists($output)) {
            throw new PharStaleOutputException($output);
        }

        $alias = basename($output);
        $phar = new Phar($output);
        $phar->setSignatureAlgorithm(Phar::SHA256);
        $phar->startBuffering();
        $files = $phar->buildFromIterator(self::files($appDirReal, $roots, $output), $appDirReal);
        // The entry is on disk - checked above - but the filter decides what reaches the archive.
        if (! isset($phar[$entry])) {
            throw new PharEntryNotPackedException($appDirReal . '/' . $entry);
        }

        $phar->setStub(sprintf(
            '<?php Phar::mapPhar(%s); require %s; __HALT_COMPILER();',
            var_export($alias, true),
            var_export('phar://' . $alias . '/' . $entry, true),
        ));
        $phar->stopBuffering();
        clearstatcache(true, $output);

        return new PharReport($output, (int) filesize($output), count($files), self::writeDirOf($hostRecord));
    }

    /**
     * Application roots to ship, each with the DI script directory to take from it.
     *
     * Two checks per application, and both stop the build rather than the deploy:
     * its scripts must write outside the archive (an import compiled without APP_WRITE_DIR
     * writes under its own tree, inside it), and they must write exactly where the
     * declaration that boots them derives - a marker that says otherwise means the build
     * and the AppModule read different write directories, and the boot would recompile.
     *
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
        // Both forms of the tree root: the marker holds textual paths, so "inside the
        // archive" must catch a tmpDir spelled through a symlink (var/ -> elsewhere) as
        // well as one spelled through a deployment link (current -> releases/42).
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
     * {writeDir}/{Vendor}/{Project}/{context}/tmp names its write directory; another tmpDir does not.
     *
     * @return non-empty-string|null
     */
    private static function writeDirOf(CompileRecord $record): string|null
    {
        $tmpDir = self::normalize($record->tmpDir);
        $suffix = '/' . str_replace('\\', '/', $record->appName) . '/' . $record->context . '/tmp';
        if (! str_ends_with($tmpDir, $suffix)) {
            return null;
        }

        $writeDir = substr($tmpDir, 0, -strlen($suffix));

        return $writeDir === '' ? null : $writeDir;
    }

    /**
     * Everything under appDir except what must not ship.
     *
     * No file whose name starts with .env ships, wherever it sits - `.env.local` and
     * `.env.production` carry secrets as readily as `.env`, and the values a boot needs are
     * compiled into the DI scripts. Of each application's var/ only
     * its DI script directory goes in, minus Ray.Compiler's build noise. autoload.php and
     * preload.php hold build-time absolute paths a phar boot cannot use. A symlinked
     * directory stops the build: Phar::buildFromIterator() cannot pack it.
     *
     * @param non-empty-string                                    $appDir
     * @param non-empty-array<non-empty-string, non-empty-string> $roots  app root => script dir
     * @param non-empty-string                                    $output
     *
     * @return Iterator<SplFileInfo>
     */
    private static function files(string $appDir, array $roots, string $output): Iterator
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
