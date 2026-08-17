<?php

declare(strict_types=1);

namespace BEAR\Package;

use BEAR\Sunday\Compile\CompileStepInterface;

use function is_dir;

/** Records what it was handed, and creates nothing itself */
final class FakeRecordingStep implements CompileStepInterface
{
    public string|null $stepDir = null;

    public bool $stepDirExisted = false;

    public function __invoke(string $stepDir): int
    {
        $this->stepDir = $stepDir;
        $this->stepDirExisted = is_dir($stepDir);

        return 0;
    }
}
