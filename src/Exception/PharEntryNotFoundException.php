<?php

declare(strict_types=1);

namespace BEAR\Package\Exception;

/**
 * The entry the stub would run is not on disk.
 *
 * The stub requires this path on every boot, so a missing one stops the build instead. An
 * application whose entry is not `public/index.php` passes its own to `Compiler::phar()`.
 */
final class PharEntryNotFoundException extends LogicException
{
    public function __construct(string $entry)
    {
        parent::__construct($entry);
    }
}
