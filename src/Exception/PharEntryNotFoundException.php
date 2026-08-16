<?php

declare(strict_types=1);

namespace BEAR\Package\Exception;

/**
 * The entry the stub would run is not on disk.
 *
 * The stub requires this path on every boot, so a missing one stops the build instead. An
 * application whose entry is not `public/index.php` passes its own to `Compiler::phar()`.
 *
 * @see https://bearsunday.github.io/manuals/1.0/en/phar.html#when-the-build-stops
 */
final class PharEntryNotFoundException extends LogicException
{
    public function __construct(string $entry)
    {
        parent::__construct($entry);
    }
}
