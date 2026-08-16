<?php

declare(strict_types=1);

namespace BEAR\Package\Exception;

/**
 * A directory in the tree is a symlink.
 *
 * `Phar::buildFromIterator()` packs files, not links, and following one packs a tree that is
 * not the deployed one. Install without symlinks - Composer path repositories take
 * `{"symlink": false}`.
 *
 * @see https://bearsunday.github.io/manuals/1.0/en/phar.html#when-the-build-stops
 */
final class PharSymlinkedDirectoryException extends LogicException
{
    public function __construct(string $path)
    {
        parent::__construct($path);
    }
}
