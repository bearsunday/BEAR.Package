<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Error;

use BEAR\AppMeta\AbstractAppMeta;
use Override;

use function file_put_contents;
use function is_link;
use function is_writable;
use function sprintf;
use function symlink;
use function unlink;

final class FileLogRefWriter implements LogRefWriterInterface
{
    public function __construct(
        private AbstractAppMeta $appMeta,
    ) {
    }

    #[Override]
    public function write(LogRef $logRef, string $detail): void
    {
        $logRefFile = sprintf('%s/logref.%s.log', $this->appMeta->logDir, (string) $logRef);
        @file_put_contents($logRefFile, $detail);
        $linkFile = sprintf('%s/last.logref.log', $this->appMeta->logDir);
        is_link($linkFile) && is_writable($linkFile) && @unlink($linkFile);
        @symlink($logRefFile, $linkFile);
    }
}
