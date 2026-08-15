<?php

declare(strict_types=1);

namespace BEAR\Package\Exception;

use function sprintf;

final class PharNotCompiledException extends LogicException
{
    public function __construct(string $scriptDir)
    {
        parent::__construct(sprintf(
            'No compiled DI scripts in "%s": compile the context before packing it.',
            $scriptDir,
        ));
    }
}
