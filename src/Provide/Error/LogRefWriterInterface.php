<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Error;

interface LogRefWriterInterface
{
    /**
     * Called while an error is already being handled: an implementation reports no failure of
     * its own, and no caller depends on the detail having been recorded.
     */
    public function write(LogRef $logRef, string $detail): void;
}
