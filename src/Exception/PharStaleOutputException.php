<?php

declare(strict_types=1);

namespace BEAR\Package\Exception;

/**
 * A previous archive survived at the output path.
 *
 * `new Phar()` opens an existing file and adds to it, so the entries of the last build would
 * ship with the new ones. Remove the path, or pack somewhere else.
 *
 * @see \BEAR\Package\Compiler\PharBuilder
 * @codeCoverageIgnore thrown only where a phar is written, which no coverage run does
 * @see https://bearsunday.github.io/manuals/1.0/en/phar.html#when-the-build-stops
 */
final class PharStaleOutputException extends RuntimeException
{
    public function __construct(string $output)
    {
        parent::__construct($output);
    }
}
