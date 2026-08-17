<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use ArrayObject;
use BEAR\Package\Exception\PharEntryNotFoundException;
use BEAR\Package\Exception\PharEntryNotPackedException;
use BEAR\Package\Exception\PharImportOutsideTreeException;
use BEAR\Package\Exception\PharImportsUnreadableException;
use BEAR\Package\Exception\PharNotCompiledException;
use BEAR\Package\Exception\PharStaleOutputException;
use BEAR\Package\Exception\PharWriteDirMismatchException;
use BEAR\Package\Exception\PharWritesInsideArchiveException;
use BEAR\Package\Injector\CompiledScripts;
use BEAR\Package\Injector\CompileMarker;
use BEAR\Package\Injector\CompileRecord;
use BEAR\Package\Types;
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
use function preg_match;
use function realpath;
use function sprintf;
use function unlink;
use function var_export;

/**
 * Write the archive PharManifest describes.
 *
 * Every refusal is decided before a Phar is touched; only pack() needs -d phar.readonly=0.
 *
 * @see PharManifest
 * @psalm-import-type AppDir from Types
 * @psalm-import-type BuildDir from Types
 * @psalm-import-type Context from Types
 * @psalm-import-type StubEntry from Types
 * @psalm-import-type PharPath from Types
 */
final class PharBuilder
{
    /** A leading slash, a UNC share, or a drive letter - the spellings realpath() would not change. */
    private const ABSOLUTE = '#^(/|\\\\|[A-Za-z]:[/\\\\])#';

    /**
     * @param Context       $context
     * @param AppDir        $appDir
     * @param StubEntry     $entry   relative to appDir
     * @param PharPath|null $output  default {appDir}/app.phar
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
        /** @var AppDir $appDirReal psalm's realpath stub says string; phpstan's already says non-empty */
        if (! is_file($appDirReal . '/' . $entry)) {
            throw new PharEntryNotFoundException($appDirReal . '/' . $entry);
        }

        // The host marker is read before the container is asked for its imports, so an
        // uncompiled tree reports "not compiled", not a script-directory error.
        $hostDir = CompiledScripts::dir($appDirReal, $context);
        $hostRecord = CompileMarker::read($hostDir);
        if ($hostRecord === null) {
            throw new PharNotCompiledException($hostDir);
        }

        $roots = PharManifest::roots($appDirReal, $context, ImportedApps::of($hostDir));
        $output ??= $appDirReal . '/app.phar';
        @mkdir(dirname($output), 0777, true);
        // PharManifest::files() excludes the archive by path, and the iterator yields absolute ones.
        $output = self::absolute($output);
        @unlink($output);
        clearstatcache(true, $output);
        // new Phar() on a surviving file adds to it, and the stale entries would ship unnoticed.
        if (file_exists($output)) {
            throw new PharStaleOutputException($output);
        }

        return self::pack($appDirReal, $roots, $entry, $output, $hostRecord); // @codeCoverageIgnore
    }

    /**
     * @param PharPath $path
     *
     * @return PharPath
     */
    private static function absolute(string $path): string
    {
        if (preg_match(self::ABSOLUTE, $path)) {
            return $path;
        }

        $dir = realpath(dirname($path));

        return $dir === false ? $path : $dir . '/' . basename($path);
    }

    /**
     * @param AppDir                            $appDir
     * @param non-empty-array<AppDir, BuildDir> $roots
     * @param StubEntry                         $entry
     * @param PharPath                          $output
     *
     * @throws PharEntryNotPackedException
     *
     * @codeCoverageIgnore writing a phar takes -d phar.readonly=0, which no coverage run has
     */
    private static function pack(string $appDir, array $roots, string $entry, string $output, CompileRecord $record): PharReport
    {
        $alias = basename($output);
        $phar = new Phar($output);
        $phar->setSignatureAlgorithm(Phar::SHA256);
        $phar->startBuffering();
        /** @var ArrayObject<int, string> $symlinked */
        $symlinked = new ArrayObject();
        $files = $phar->buildFromIterator(PharManifest::files($appDir, $roots, $output, $entry, $symlinked), $appDir);
        // The entry is on disk - checked above - but the filter decides what reaches the archive.
        if (! isset($phar[$entry])) {
            throw new PharEntryNotPackedException($appDir . '/' . $entry);
        }

        $phar->setStub(sprintf(
            '<?php Phar::mapPhar(%s); require %s; __HALT_COMPILER();',
            var_export($alias, true),
            var_export('phar://' . $alias . '/' . $entry, true),
        ));
        $phar->stopBuffering();
        clearstatcache(true, $output);

        return new PharReport(
            $output,
            (int) filesize($output),
            count($files),
            $record->writeDir,
            PharManifest::notPacked($appDir, $roots, $entry),
            PharManifest::symlinkedDirs($symlinked),
        );
    }
}
