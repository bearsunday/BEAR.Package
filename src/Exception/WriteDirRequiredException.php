<?php

declare(strict_types=1);

namespace BEAR\Package\Exception;

use function sprintf;

final class WriteDirRequiredException extends LogicException
{
    public function __construct(string $appDir)
    {
        parent::__construct(sprintf(
            'The application directory "%s" is a stream URI, which cannot hold the tmp and log directories. Pass an absolute write directory as the last argument.',
            $appDir,
        ));
    }
}
