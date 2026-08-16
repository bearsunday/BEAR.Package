<?php

declare(strict_types=1);

namespace BEAR\Package\Injector;

use BEAR\Package\Types;

/**
 * What a compile wrote into a script directory.
 *
 * @see CompileMarker
 * @psalm-import-type AppName from Types
 * @psalm-import-type Context from Types
 * @psalm-import-type TmpDir from Types
 */
final class CompileRecord
{
    /**
     * @param AppName $appName
     * @param Context $context
     * @param TmpDir  $tmpDir
     */
    public function __construct(
        public readonly string $appName,
        public readonly string $context,
        public readonly string $tmpDir,
        public readonly int $time,
    ) {
    }
}
