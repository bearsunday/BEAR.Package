<?php

declare(strict_types=1);

namespace FakeVendor\HelloWorld;

use BEAR\Sunday\Compile\CompileStepInterface;

use function file_put_contents;

/** Writes two artifacts, and never creates $stepDir: the caller owns it */
final class FakeAlphaStep implements CompileStepInterface
{
    public function __invoke(string $stepDir): int
    {
        file_put_contents($stepDir . '/alpha-1.txt', $stepDir);
        file_put_contents($stepDir . '/alpha-2.txt', $stepDir);

        return 2;
    }
}
