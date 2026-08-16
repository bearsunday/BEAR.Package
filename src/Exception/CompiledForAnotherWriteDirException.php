<?php

declare(strict_types=1);

namespace BEAR\Package\Exception;

use function sprintf;

/**
 * The scripts on disk were compiled for another write directory, and cannot be replaced.
 *
 * Recompiling writes to the script directory, which an archive or an immutable image does not
 * allow. Start with `APP_WRITE_DIR` set to what the build used, or compile again for this one.
 */
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
