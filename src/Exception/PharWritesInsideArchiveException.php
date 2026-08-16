<?php

declare(strict_types=1);

namespace BEAR\Package\Exception;

use function sprintf;

/**
 * An application in the tree was compiled to write inside it.
 *
 * The archive is read-only at run time, so scripts that write to their own `var/` would try to
 * recompile into it on the first boot. Compile with a `$writeDir` outside the tree; an imported
 * application takes its own, in the `ImportApp` its AppModule declares.
 *
 * @see https://bearsunday.github.io/manuals/1.0/en/phar.html#when-the-build-stops
 */
final class PharWritesInsideArchiveException extends LogicException
{
    public function __construct(string $appDir, string $tmpDir)
    {
        parent::__construct(sprintf('%s, writes %s', $appDir, $tmpDir));
    }
}
