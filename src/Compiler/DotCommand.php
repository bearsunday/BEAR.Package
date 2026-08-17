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

    /**
     * $output is captured and never read: that is what keeps dot's stdout, and the shell's
     * "not found" on 2>&1, out of the compile output. passthru() here would print both.
     *
     * @SuppressWarnings("PHPMD.UnusedLocalVariable")
     */
    public function __invoke(string $dotFile, string $svgFile): bool
    {
        exec(
            sprintf('%s -Tsvg %s -o %s 2>&1', escapeshellarg($this->command), escapeshellarg($dotFile), escapeshellarg($svgFile)),
            $output,
            $status,
        );

        return $status === 0 && is_file($svgFile);
    }
}
