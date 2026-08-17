<?php

declare(strict_types=1);

namespace FakeVendor\HelloWorld;

use BEAR\Sunday\Compile\CompileStepInterface;

final class FakeFailingStep implements CompileStepInterface
{
    public const MESSAGE = 'the template engine could not compile';

    public function __invoke(string $stepDir): int
    {
        throw new FakeCompileStepException(self::MESSAGE);
    }
}
