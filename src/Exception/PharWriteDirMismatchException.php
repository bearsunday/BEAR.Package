<?php

declare(strict_types=1);

namespace BEAR\Package\Exception;

use function sprintf;

/**
 * The scripts of an application write where its declaration does not.
 *
 * The build read one write directory while the AppModule that boots the import derives another,
 * so the boot would find a marker for a directory it does not use, and recompile.
 *
 * @see https://bearsunday.github.io/manuals/1.0/en/phar.html#when-the-build-stops
 */
final class PharWriteDirMismatchException extends LogicException
{
    public function __construct(string $appDir, string $compiledFor, string $expected)
    {
        parent::__construct(sprintf('%s, compiled %s, declared %s', $appDir, $compiledFor, $expected));
    }
}
