<?php

declare(strict_types=1);

namespace BEAR\Package\Exception;

use function sprintf;

final class CompiledForAnotherWriteDirException extends RuntimeException
{
    public function __construct(string $scriptDir, string|null $compiledFor, string $tmpDir)
    {
        parent::__construct(sprintf(
            'The read-only scripts in "%s" write to %s, this boot to "%s".',
            $scriptDir,
            $compiledFor === null ? 'a directory this version cannot read' : sprintf('"%s"', $compiledFor),
            $tmpDir,
        ));
    }
}
