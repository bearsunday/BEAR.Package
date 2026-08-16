<?php

declare(strict_types=1);

namespace BEAR\Package\Exception;

use function sprintf;

final class PharWritesInsideArchiveException extends LogicException
{
    public function __construct(string $appDir, string $tmpDir)
    {
        parent::__construct(sprintf(
            'The scripts of "%s" write to "%s", inside the archive.',
            $appDir,
            $tmpDir,
        ));
    }
}
