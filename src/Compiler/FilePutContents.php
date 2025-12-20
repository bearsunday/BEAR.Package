<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use ArrayObject;
use BEAR\Package\Types;

use function file_exists;
use function file_put_contents;

/** @psalm-import-type OverwrittenFiles from Types */
final class FilePutContents
{
    /** @param OverwrittenFiles $overwritten For debugging */
    public function __construct(
        private ArrayObject $overwritten,  // @phpstan-ignore-line
    ) {
    }

    /**
     * @psalm-taint-sink file $fileName
     * @psalm-taint-sink file $content
     */
    public function __invoke(string $fileName, string $content): void
    {
        if (file_exists($fileName)) {
            /** @psalm-suppress NullArgument */
            $this->overwritten[] = $fileName;
        }

        file_put_contents($fileName, $content);
    }
}
