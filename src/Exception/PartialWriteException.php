<?php

declare(strict_types=1);

namespace BEAR\Package\Exception;

use function sprintf;

/**
 * A generated file was written short.
 *
 * `file_put_contents()` reports a byte count, not success: a full disk or a quota writes part of
 * the content and returns. Half a `preload.php` is a parse error on every boot, so the compile
 * stops here instead of shipping one.
 */
final class PartialWriteException extends RuntimeException
{
    public function __construct(string $fileName, int $written, int $expected)
    {
        parent::__construct(sprintf('Wrote %d of %d bytes to "%s".', $written, $expected, $fileName));
    }
}
