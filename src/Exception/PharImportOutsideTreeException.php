<?php

declare(strict_types=1);

namespace BEAR\Package\Exception;

use function sprintf;

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
