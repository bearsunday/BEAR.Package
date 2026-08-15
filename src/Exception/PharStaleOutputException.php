<?php

declare(strict_types=1);

namespace BEAR\Package\Exception;

use function sprintf;

/**
 * @see \BEAR\Package\Compiler\PharBuilder the only thrower, and the only code a coverage run cannot execute
 * @codeCoverageIgnore
 */
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
