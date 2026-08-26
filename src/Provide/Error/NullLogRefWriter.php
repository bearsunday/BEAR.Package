<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Error;

use Override;

final class NullLogRefWriter implements LogRefWriterInterface
{
    #[Override]
    public function write(LogRef $logRef, string $detail): void
    {
    }
}
