<?php

declare(strict_types=1);

namespace BEAR\Package\Exception;

use function sprintf;

final class PharWriteDirMismatchException extends LogicException
{
    public function __construct(string $appDir, string $compiledFor, string $expected)
    {
        parent::__construct(sprintf(
            'The scripts of "%s" write to "%s", its declaration derives "%s".',
            $appDir,
            $compiledFor,
            $expected,
        ));
    }
}
