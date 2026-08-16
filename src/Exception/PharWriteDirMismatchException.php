<?php

declare(strict_types=1);

namespace BEAR\Package\Exception;

use function sprintf;

/**
 * An import's scripts write somewhere other than under the host's write directory.
 *
 * The import was compiled for a directory the host does not use, so its boot would derive
 * another one and try to recompile - inside the archive, where it cannot.
 *
 * @see https://bearsunday.github.io/manuals/1.0/en/phar.html#when-the-build-stops
 */
final class PharWriteDirMismatchException extends LogicException
{
    public function __construct(string $appDir, string $compiledFor, string $hostWriteDir)
    {
        parent::__construct(sprintf('%s, compiled %s, host %s', $appDir, $compiledFor, $hostWriteDir));
    }
}
