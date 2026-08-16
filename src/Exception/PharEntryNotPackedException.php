<?php

declare(strict_types=1);

namespace BEAR\Package\Exception;

use function sprintf;

/**
 * @see \BEAR\Package\Compiler\PharBuilder the only thrower, and the only code a coverage run cannot execute
 * @codeCoverageIgnore
 */
final class PharEntryNotPackedException extends LogicException
{
    public function __construct(string $entry)
    {
        parent::__construct(sprintf(
            'The stub entry "%s" is not in the archive.',
            $entry,
        ));
    }
}
