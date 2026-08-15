<?php

declare(strict_types=1);

namespace BEAR\Package\Exception;

use function sprintf;

final class PharStaleOutputException extends RuntimeException
{
    public function __construct(string $output)
    {
        parent::__construct(sprintf(
            'The previous archive "%s" could not be removed, and packing into it would ship its stale entries. '
            . 'Remove it, or pass another output path.',
            $output,
        ));
    }
}
