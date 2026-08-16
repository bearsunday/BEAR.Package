<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use BEAR\Package\Types;

/**
 * What PharBuilder wrote, for the worker to report.
 *
 * @see PharBuilder
 * @codeCoverageIgnore built only where a phar is written, which no coverage run does
 * @psalm-import-type PharPath from Types
 * @psalm-import-type WriteDir from Types
 */
final class PharReport
{
    /**
     * @param PharPath      $path
     * @param WriteDir|null $writeDir the directory the packed scripts write to, when they name one
     */
    public function __construct(
        public readonly string $path,
        public readonly int $bytes,
        public readonly int $files,
        public readonly string|null $writeDir,
    ) {
    }
}
