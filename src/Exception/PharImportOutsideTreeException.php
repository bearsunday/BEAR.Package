<?php

declare(strict_types=1);

namespace BEAR\Package\Exception;

use function sprintf;

/**
 * An imported application lies outside the tree being packed.
 *
 * Only what is under the application directory reaches the archive, so an import has to live in
 * the project or in its vendor directory to ship with it.
 */
final class PharImportOutsideTreeException extends LogicException
{
    public function __construct(string $importDir, string $archiveDir)
    {
        parent::__construct(sprintf(
            'The imported application "%s" lies outside "%s".',
            $importDir,
            $archiveDir,
        ));
    }
}
