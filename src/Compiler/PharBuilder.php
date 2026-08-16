<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use BEAR\Package\Exception\PharEntryNotFoundException;
use BEAR\Package\Exception\PharEntryNotPackedException;
use BEAR\Package\Exception\PharImportOutsideTreeException;
use BEAR\Package\Exception\PharImportsUnreadableException;
use BEAR\Package\Exception\PharNotCompiledException;
use BEAR\Package\Exception\PharStaleOutputException;
use BEAR\Package\Exception\PharWriteDirMismatchException;
use BEAR\Package\Exception\PharWritesInsideArchiveException;
use BEAR\Package\Injector\AppDirs;
use BEAR\Package\Injector\CompileMarker;
use Phar;

use function assert;
use function basename;
use function clearstatcache;
use function count;
use function dirname;
use function file_exists;
use function filesize;
use function is_file;
use function mkdir;
use function realpath;
use function sprintf;
use function unlink;
use function var_export;

/**
 * Write the archive PharManifest describes.
 *
 * Runs in bin/phar-worker.php, which the tests drive: phar.readonly is INI_SYSTEM.
 *
 * @see PharManifest
 * @codeCoverageIgnore writing a phar takes -d phar.readonly=0, which no coverage run has
 */
final class PharBuilder
{
    /**
     * @param non-empty-string      $context
     * @param non-empty-string      $appDir
     * @param non-empty-string      $entry   stub entry, relative to appDir
     * @param non-empty-string|null $output  archive path; default {appDir}/var/build/{context}.phar
     *
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
        // Native form for the filesystem and Phar APIs; PharManifest normalizes separators.
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

        $roots = PharManifest::roots($appDirReal, $context, ImportedApps::of($hostDir));
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
        $files = $phar->buildFromIterator(PharManifest::files($appDirReal, $roots, $output), $appDirReal);
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

        return new PharReport($output, (int) filesize($output), count($files), PharManifest::writeDirOf($hostRecord));
    }
}
