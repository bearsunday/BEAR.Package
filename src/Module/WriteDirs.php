<?php

declare(strict_types=1);

namespace BEAR\Package\Module;

use BEAR\AppMeta\Types;
use BEAR\Package\Exception\DeclaredWriteDirException;

use function preg_match;
use function rtrim;

/**
 * Where an application writes, as the application itself declares it.
 *
 * @psalm-import-type LogDir from Types
 * @psalm-import-type TmpDir from Types
 */
final class WriteDirs
{
    /** A leading slash, a UNC share, a drive letter, or a stream scheme. */
    private const ABSOLUTE = '#^(/|\\\\\\\\|[A-Za-z]:[/\\\\]|[A-Za-z][A-Za-z0-9+.\-]*://)#';

    /** @var TmpDir */
    public readonly string $tmpDir;

    /** @var LogDir */
    public readonly string $logDir;

    /**
     * Takes what an application wrote, which nothing has checked yet.
     *
     * @throws DeclaredWriteDirException
     */
    public function __construct(string $tmpDir, string $logDir)
    {
        $this->tmpDir = self::absolute($tmpDir);
        $this->logDir = self::absolute($logDir);
    }

    /**
     * @return non-empty-string
     *
     * @throws DeclaredWriteDirException
     */
    private static function absolute(string $dir): string
    {
        $dir = rtrim($dir, '/\\');
        if ($dir === '' || ! preg_match(self::ABSOLUTE, $dir)) {
            throw new DeclaredWriteDirException($dir);
        }

        return $dir;
    }
}
