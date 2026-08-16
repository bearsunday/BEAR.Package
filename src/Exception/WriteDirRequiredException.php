<?php

declare(strict_types=1);

namespace BEAR\Package\Exception;

use function sprintf;

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
