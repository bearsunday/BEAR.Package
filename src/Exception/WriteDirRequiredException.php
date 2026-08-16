<?php

declare(strict_types=1);

namespace BEAR\Package\Exception;

use function sprintf;

/**
 * The application directory is a stream URI, which has nowhere to put tmp and log.
 *
 * An artifact holds the application read-only, so the writable base has to be named: pass an
 * absolute write directory to the injector.
 */
final class WriteDirRequiredException extends LogicException
{
    public function __construct(string $appDir)
    {
        parent::__construct(sprintf(
            'The application directory "%s" is a stream URI, which holds no tmp and log.',
            $appDir,
        ));
    }
}
