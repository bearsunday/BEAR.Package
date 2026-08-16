<?php

declare(strict_types=1);

namespace BEAR\Package\Exception;

use function sprintf;

/**
 * The write directory is not an absolute path.
 *
 * A relative path resolves against the current directory, which differs between the compile and
 * the request, so the boot would look for its scripts somewhere else.
 */
final class InvalidWriteDirException extends LogicException
{
    public function __construct(string $writeDir)
    {
        parent::__construct(sprintf(
            'The write directory must be an absolute path, "%s" given.',
            $writeDir,
        ));
    }
}
