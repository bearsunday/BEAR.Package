<?php

declare(strict_types=1);

namespace BEAR\Package\Exception;

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
        parent::__construct($appDir);
    }
}
