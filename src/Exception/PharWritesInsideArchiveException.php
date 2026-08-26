<?php

declare(strict_types=1);

namespace BEAR\Package\Exception;

use function sprintf;

/**
 * An application in the tree was compiled to write inside it.
 *
 * The archive is read-only at run time, so scripts that write to their own `var/` would try to
 * recompile into it on the first boot. Install `ReadOnlyAppModule` in the application's own
 * ProdModule, naming a directory outside the tree or naming none at all.
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
