<?php

declare(strict_types=1);

namespace FakeVendor\HelloWorld;

use BEAR\Sunday\Compile\CompileStepInterface;

use function file_put_contents;

/** Writes one artifact, and never creates $stepDir: the caller owns it */
final class FakeBetaStep implements CompileStepInterface
{
    public function __invoke(string $stepDir): int
    {
        file_put_contents($stepDir . '/beta-1.txt', $stepDir);

        return 1;
    }
}
