<?php

declare(strict_types=1);

namespace BEAR\Package\Exception;

use function sprintf;

final class PharEmptyArchiveException extends LogicException
{
    public function __construct(string $appDir)
    {
        parent::__construct(sprintf(
            'Packing "%s" selected no files at all: PHP writes no archive for an empty file set. This is a bug in the file filter, not in the application.',
            $appDir,
        ));
    }
}
