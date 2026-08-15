<?php

declare(strict_types=1);

namespace BEAR\Package\Exception;

use function sprintf;

final class PharWritesInsideArchiveException extends LogicException
{
    public function __construct(string $appDir, string $tmpDir)
    {
        parent::__construct(sprintf(
            'The scripts of "%s" were compiled to write to "%s", inside the archive, which is read-only at run time. Compile with APP_WRITE_DIR set - an imported application reads it in its AppModule too.',
            $appDir,
            $tmpDir,
        ));
    }
}
