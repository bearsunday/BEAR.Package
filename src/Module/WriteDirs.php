<?php

declare(strict_types=1);

namespace BEAR\Package\Module;

use BEAR\AppMeta\Types;

/**
 * Where an application writes, as the application itself declares it.
 *
 * @psalm-import-type LogDir from Types
 * @psalm-import-type TmpDir from Types
 */
final class WriteDirs
{
    /**
     * @param TmpDir $tmpDir
     * @param LogDir $logDir
     */
    public function __construct(
        public readonly string $tmpDir,
        public readonly string $logDir,
    ) {
    }
}
