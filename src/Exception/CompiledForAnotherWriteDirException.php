<?php

declare(strict_types=1);

namespace BEAR\Package\Exception;

use function sprintf;

final class CompiledForAnotherWriteDirException extends RuntimeException
{
    public function __construct(string $scriptDir, string|null $compiledFor, string $tmpDir)
    {
        parent::__construct(sprintf(
            'The scripts in "%s" were compiled for %s, this boot writes to "%s", and nothing can be '
            . 'rewritten here. Start with APP_WRITE_DIR set to what the build used, or compile again for this one.',
            $scriptDir,
            $compiledFor === null ? 'no write directory this version can read' : sprintf('"%s"', $compiledFor),
            $tmpDir,
        ));
    }
}
