<?php

declare(strict_types=1);

namespace BEAR\Package\Exception;

use function sprintf;

final class PharSymlinkedDirectoryException extends LogicException
{
    public function __construct(string $path)
    {
        parent::__construct(sprintf(
            'Cannot pack the directory symlink "%s".',
            $path,
        ));
    }
}
