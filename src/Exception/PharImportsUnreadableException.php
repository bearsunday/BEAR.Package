<?php

declare(strict_types=1);

namespace BEAR\Package\Exception;

use function sprintf;

/**
 * The compiled container declares its imports in a form this version cannot read.
 *
 * That declaration is what the packer trusts instead of scanning the tree, so it stops rather
 * than ship an archive with an application missing. Recompile with the version that packs it.
 */
final class PharImportsUnreadableException extends RuntimeException
{
    public function __construct(string $scriptDir)
    {
        parent::__construct(sprintf(
            'Unreadable import declaration in the compiled container "%s".',
            $scriptDir,
        ));
    }
}
