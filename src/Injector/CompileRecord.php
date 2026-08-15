<?php

declare(strict_types=1);

namespace BEAR\Package\Injector;

/**
 * What a compile wrote into a script directory: which application, which context,
 * and the writable directory its bindings hold.
 *
 * @see CompileMarker
 */
final class CompileRecord
{
    /**
     * @param non-empty-string $appName
     * @param non-empty-string $context
     * @param non-empty-string $tmpDir
     */
    public function __construct(
        public readonly string $appName,
        public readonly string $context,
        public readonly string $tmpDir,
        public readonly int $time,
    ) {
    }
}
