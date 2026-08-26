<?php

declare(strict_types=1);

namespace BEAR\Package;

use BEAR\Package\Provide\Error\LogRef;
use BEAR\Package\Provide\Error\LogRefWriterInterface;
use Override;

final class FakeLogRefWriter implements LogRefWriterInterface
{
    /** @var array<string, string> */
    public array $written = [];

    #[Override]
    public function write(LogRef $logRef, string $detail): void
    {
        $this->written[(string) $logRef] = $detail;
    }
}
