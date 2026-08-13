<?php

declare(strict_types=1);

namespace BEAR\Package\Exception;

use function sprintf;

final class WriteDirMismatchException extends LogicException
{
    public function __construct(string $compileTmpDir, string $injectorTmpDir)
    {
        parent::__construct(sprintf(
            'The compile would write to "%s" while the injector writes to "%s". Pass the same write directory to the injector and to Compiler::fromInjector(), or the boot recompiles on every cold start.',
            $compileTmpDir,
            $injectorTmpDir,
        ));
    }
}
