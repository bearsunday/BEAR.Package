<?php

declare(strict_types=1);

namespace BEAR\Package\Exception;

use function sprintf;

/**
 * The compile and the injector were given different write directories.
 *
 * The compiled scripts hold the compile's paths, so the injector would find a marker for a
 * directory it does not use, and recompile on every cold start.
 */
final class WriteDirMismatchException extends LogicException
{
    public function __construct(string $compileTmpDir, string $injectorTmpDir)
    {
        parent::__construct(sprintf(
            'The compile writes to "%s", the injector to "%s".',
            $compileTmpDir,
            $injectorTmpDir,
        ));
    }
}
