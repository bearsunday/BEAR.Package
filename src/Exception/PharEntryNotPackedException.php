<?php

declare(strict_types=1);

namespace BEAR\Package\Exception;

use function sprintf;

final class PharEntryNotPackedException extends LogicException
{
    public function __construct(string $entry)
    {
        parent::__construct(sprintf(
            'The entry "%s" exists but does not ship: the archive never holds a .env file, autoload.php, '
            . 'preload.php, tests, or a var/ path other than the DI scripts. Pass an entry the archive carries.',
            $entry,
        ));
    }
}
