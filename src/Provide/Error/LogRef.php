<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Error;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\Sunday\Extension\Router\RouterMatch;
use Stringable;
use Throwable;

use function file_put_contents;
use function is_link;
use function is_writable;
use function sprintf;
use function symlink;
use function unlink;

final class LogRef implements Stringable
{
    private string $ref;

    public function __construct(Throwable $e)
    {
        // phpcs:ignore SlevomatCodingStandard.Namespaces.ReferenceUsedNamesOnly.ReferenceViaFallbackGlobalName
        $this->ref = hash('crc32b', $e::class . $e->getMessage() . $e->getFile() . $e->getLine());
    }

    public function __toString(): string
    {
        return $this->ref;
    }

    public function log(Throwable $e, RouterMatch $request, AbstractAppMeta $appMeta): void
    {
        $logRefFile = sprintf('%s/logref.%s.log', $appMeta->logDir, $this->ref);
        $log = (string) new ExceptionAsString($e, $request);
        @file_put_contents($logRefFile, $log);
        $linkFile = sprintf('%s/last.logref.log', $appMeta->logDir);
        is_link($linkFile) && is_writable($linkFile) && @unlink($linkFile);
        @symlink($logRefFile, $linkFile);
    }
}
