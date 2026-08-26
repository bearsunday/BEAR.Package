<?php

declare(strict_types=1);

namespace BEAR\Package\Exception;

use function sprintf;

/**
 * The boot that records preload.php could not run, or did not run against the artifact.
 *
 * preload.php is measured, not inferred: if the measurement is wrong the compile stops here
 * rather than shipping an artifact whose preload describes a different application state.
 */
final class PreloadRecordException extends RuntimeException
{
    public static function workerFailed(string $context, int $exitCode): self
    {
        return new self(sprintf(
            'Recording preload.php failed: booting "%s" from the compiled scripts exited with %d.',
            $context,
            $exitCode,
        ));
    }

    /** preload.php describes a boot that reads compiled scripts; a per-request context has none. */
    public static function notCompiled(string $context): self
    {
        return new self(sprintf(
            'Recording preload.php needs a compiled context; "%s" assembles the container on each request.',
            $context,
        ));
    }

    /**
     * Without a matching marker the boot compiles on demand, and the recording measures that
     * compile instead of a request - the exact error this pipeline exists to remove.
     */
    public static function scriptsNotCurrent(string $scriptDir, string $context): self
    {
        return new self(sprintf(
            'Recording preload.php needs the compiled scripts in "%s" to be current for "%s".',
            $scriptDir,
            $context,
        ));
    }
}
