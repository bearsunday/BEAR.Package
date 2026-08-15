<?php

declare(strict_types=1);

namespace BEAR\Package\Exception;

use function sprintf;

final class PharSymlinkedDirectoryException extends LogicException
{
    public function __construct(string $path)
    {
        parent::__construct(sprintf(
            'The directory symlink "%s" cannot be packed: an archive holds files, not links, and silently following it could pack a tree that is not deployed. Replace it with a real directory, or install dependencies without symlinks (composer path repositories: "symlink": false).',
            $path,
        ));
    }
}
