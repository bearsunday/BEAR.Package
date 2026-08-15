<?php

declare(strict_types=1);

namespace BEAR\Package\Exception;

use function sprintf;

final class PharImportsUnreadableException extends RuntimeException
{
    public function __construct(string $scriptDir)
    {
        parent::__construct(sprintf(
            'The compiled container in "%s" holds an import declaration this version cannot read. Recompile the application with the same bear/package version that packs it.',
            $scriptDir,
        ));
    }
}
