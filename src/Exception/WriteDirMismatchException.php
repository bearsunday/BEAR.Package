<?php

declare(strict_types=1);

namespace BEAR\Package\Exception;

use function sprintf;

final class WriteDirMismatchException extends LogicException
{
    public function __construct(string $compileTmpDir, string $injectorTmpDir)
    {
        parent::__construct(sprintf(
            'The compile writes to "%s", the injector to "%s".',
            $compileTmpDir,
            $injectorTmpDir,
        ));
    }
}
