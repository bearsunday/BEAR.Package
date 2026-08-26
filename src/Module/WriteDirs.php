<?php

declare(strict_types=1);

namespace BEAR\Package\Module;

use BEAR\Package\Types;

/**
 * The write directories the module tree settled on; null leaves the answer to the booting machine.
 *
 * @psalm-import-type LogDir from Types
 * @psalm-import-type TmpDir from Types
 */
final class WriteDirs
{
    /**
     * @param TmpDir|null $tmpDir
     * @param LogDir|null $logDir
     */
    public function __construct(
        public readonly string|null $tmpDir = null,
        public readonly string|null $logDir = null,
    ) {
    }
}
