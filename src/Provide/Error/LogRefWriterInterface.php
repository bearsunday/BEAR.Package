<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Error;

interface LogRefWriterInterface
{
    /**
     * Record the rendered detail of an error under its logref.
     *
     * Called during error handling; must not fail.
     */
    public function write(LogRef $logRef, string $detail): void;
}
