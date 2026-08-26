<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use ArrayObject;
use BEAR\Package\Exception\PartialWriteException;
use BEAR\Package\Types;

use function dirname;
use function file_exists;
use function file_put_contents;
use function in_array;
use function is_dir;
use function mkdir;
use function strlen;

/** @psalm-import-type OverwrittenFiles from Types */
final class FilePutContents
{
    /** @param OverwrittenFiles $overwritten Tracks files replaced during compile (for reports). */
    public function __construct(
        private ArrayObject $overwritten,
    ) {
    }

    /** @throws PartialWriteException */
    public function __invoke(string $fileName, string $content): void
    {
        if (file_exists($fileName)) {
            /** @psalm-suppress NullArgument */
            $this->overwritten[] = $fileName;
        }

        $dir = dirname($fileName);
        is_dir($dir) || @mkdir($dir, 0777, true);

        // A full disk writes some of the bytes and reports no error. The compile would then
        // ship a truncated preload.php or autoload.php, which is a parse error on every boot.
        $written = file_put_contents($fileName, $content);
        if ($written !== strlen($content)) {
            throw new PartialWriteException($fileName, $written === false ? 0 : $written, strlen($content));
        }
    }

    public function isOverwritten(string $fileName): bool
    {
        return in_array($fileName, (array) $this->overwritten, true);
    }
}
