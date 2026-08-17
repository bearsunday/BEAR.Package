<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use function escapeshellarg;
use function exec;
use function is_file;
use function sprintf;

final class DotCommand
{
    public function __construct(
        private string $command = 'dot',
    ) {
    }

    public function __invoke(string $dotFile, string $svgFile): bool
    {
        // exec() keeps stdout out of the compile output, and 2>&1 keeps the shell's
        // "not found" message out of it too; passthru() here would print both.
        exec(
            sprintf('%s -Tsvg %s -o %s 2>&1', escapeshellarg($this->command), escapeshellarg($dotFile), escapeshellarg($svgFile)),
            $output,
            $status,
        );

        return $status === 0 && is_file($svgFile);
    }
}
