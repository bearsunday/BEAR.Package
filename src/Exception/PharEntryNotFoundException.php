<?php

declare(strict_types=1);

namespace BEAR\Package\Exception;

use function sprintf;

final class PharEntryNotFoundException extends LogicException
{
    public function __construct(string $entry)
    {
        parent::__construct(sprintf(
            'The stub entry "%s" does not exist.',
            $entry,
        ));
    }
}
