<?php

declare(strict_types=1);

namespace BEAR\Package\Exception;

/**
 * An imported application declares no write directory.
 *
 * Without one its AppModule derives tmp and log under its own directory, inside the tree being
 * packed, and the archive is read-only at run time. The `ImportApp` that declares the import
 * takes the write directory the host was built with.
 *
 * @see https://bearsunday.github.io/manuals/1.0/en/phar.html#imported-applications
 */
final class PharImportWithoutWriteDirException extends LogicException
{
    public function __construct(string $importDir)
    {
        parent::__construct($importDir);
    }
}
