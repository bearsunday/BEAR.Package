<?php

declare(strict_types=1);

namespace BEAR\Package\Module;

use BEAR\Package\Exception\DeclaredWriteDirException;
use BEAR\Package\Types;

use function preg_match;
use function rtrim;

/**
 * @psalm-import-type LogDir from Types
 * @psalm-import-type TmpDir from Types
 */
final class WriteDirs
{
    /** A leading slash, a UNC share, a drive letter, or a stream scheme. */
    private const ABSOLUTE = '#^(/|\\\\\\\\|[A-Za-z]:[/\\\\]|[A-Za-z][A-Za-z0-9+.\-]*://)#';

    /** @var TmpDir|null */
    public readonly string|null $tmpDir;

    /** @var LogDir|null */
    public readonly string|null $logDir;

    /** @throws DeclaredWriteDirException */
    public function __construct(string|null $tmpDir, string|null $logDir)
    {
        $this->tmpDir = $tmpDir === null ? null : self::absolute($tmpDir);
        $this->logDir = $logDir === null ? null : self::absolute($logDir);
    }

    /**
     * @return non-empty-string
     *
     * @throws DeclaredWriteDirException
     */
    private static function absolute(string $dir): string
    {
        if ($dir === '' || ! preg_match(self::ABSOLUTE, $dir)) {
            throw new DeclaredWriteDirException($dir);
        }

        $trimmed = rtrim($dir, '/\\');

        // One directory, one spelling - except at a root, which is all separator.
        return preg_match(self::ABSOLUTE, $trimmed) ? $trimmed : $dir;
    }
}
