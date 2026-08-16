<?php

declare(strict_types=1);

namespace BEAR\Package\Exception;

use function sprintf;

/**
 * The entry exists, but the manifest does not ship it.
 *
 * Nothing loose at the application root ships, nor `tests/`, nor a `var/` path other than the
 * DI scripts, so a stub pointing at one of those would require a path the archive has not got.
 *
 * @see \BEAR\Package\Compiler\PharBuilder
 * @codeCoverageIgnore thrown only where a phar is written, which no coverage run does
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
