<?php

declare(strict_types=1);

namespace BEAR\Package\Module;

use BEAR\Package\Types;

/**
 * What an application named, kept as it was named.
 *
 * Nothing here judges a path: a string cannot say whether one exists, is writable, or has a
 * stream wrapper behind it. Whoever writes to a directory finds that out and says so.
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
