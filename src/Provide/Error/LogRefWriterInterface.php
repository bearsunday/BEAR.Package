<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Error;

interface LogRefWriterInterface
{
    /**
     * Record the rendered detail of an error under its logref.
     *
     * Called while an error is already being handled: an implementation reports no failure of
     * its own, and the caller does not depend on the detail having been recorded anywhere.
     */
    public function write(LogRef $logRef, string $detail): void;
}
