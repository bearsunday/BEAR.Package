<?php

declare(strict_types=1);

namespace BEAR\Package\Exception;

use function sprintf;

final class PharWriteDirMismatchException extends LogicException
{
    public function __construct(string $appDir, string $compiledFor, string $expected)
    {
        parent::__construct(sprintf(
            'The compiled scripts of "%s" write to "%s", but this run derives "%s". Set APP_WRITE_DIR to what the build used, or rebuild.',
            $appDir,
            $compiledFor,
            $expected,
        ));
    }
}
