<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

/**
 * What PharBuilder wrote, for the worker to report.
 *
 * @see PharBuilder the only producer, and the only code a coverage run cannot execute
 * @codeCoverageIgnore
 */
final class PharReport
{
    /**
     * @param non-empty-string      $path
     * @param non-empty-string|null $writeDir the directory the packed scripts write to, when they name one
     */
    public function __construct(
        public readonly string $path,
        public readonly int $bytes,
        public readonly int $files,
        public readonly string|null $writeDir,
    ) {
    }
}
