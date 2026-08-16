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
            'Cannot remove the previous archive "%s".',
            $output,
        ));
    }
}
