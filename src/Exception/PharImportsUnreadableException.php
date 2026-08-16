<?php

declare(strict_types=1);

namespace BEAR\Package\Exception;

use function sprintf;

final class PharImportsUnreadableException extends RuntimeException
{
    public function __construct(string $scriptDir)
    {
        parent::__construct(sprintf(
            'Unreadable import declaration in the compiled container "%s".',
            $scriptDir,
        ));
    }
}
