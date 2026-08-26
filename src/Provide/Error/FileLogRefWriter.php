<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Error;

use BEAR\AppMeta\AbstractAppMeta;
use Override;

use function file_put_contents;
use function is_dir;
use function is_link;
use function is_writable;
use function mkdir;
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
        $logDir = $this->appMeta->logDir;
        is_dir($logDir) || @mkdir($logDir, 0777, true);
        $logRefFile = sprintf('%s/logref.%s.log', $logDir, (string) $logRef);
        @file_put_contents($logRefFile, $detail);
        $linkFile = sprintf('%s/last.logref.log', $logDir);
        is_link($linkFile) && is_writable($linkFile) && @unlink($linkFile);
        @symlink($logRefFile, $linkFile);
    }
}
