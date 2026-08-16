<?php

declare(strict_types=1);

namespace BEAR\Package\Exception;

use function sprintf;

/**
 * A directory in the tree is a symlink.
 *
 * `Phar::buildFromIterator()` packs files, not links, and following one packs a tree that is
 * not the deployed one. Install without symlinks - Composer path repositories take
 * `{"symlink": false}`.
 */
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
