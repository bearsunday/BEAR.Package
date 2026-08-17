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
        // exec() swallows stdout but not stderr: without 2>&1 the shell's "not found" prints.
        exec(
            sprintf('%s -Tsvg %s -o %s 2>&1', escapeshellarg($this->command), escapeshellarg($dotFile), escapeshellarg($svgFile)),
            result_code: $status,
        );

        return $status === 0 && is_file($svgFile);
    }
}
