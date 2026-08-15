<?php

declare(strict_types=1);

namespace BEAR\Package\Exception;

use function sprintf;

final class PharImportOutsideTreeException extends LogicException
{
    public function __construct(string $importDir, string $archiveDir)
    {
        parent::__construct(sprintf(
            'The imported application in "%s" lies outside "%s" and cannot ship in the archive. An imported application has to live in the tree being packed - in the project, or in its vendor directory.',
            $importDir,
            $archiveDir,
        ));
    }
}
