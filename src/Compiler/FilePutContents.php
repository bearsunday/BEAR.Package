<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use ArrayObject;

use function file_exists;
use function file_put_contents;

class FilePutContents
{
    /**
     * For debugging
     *
     * @var ArrayObject<int, string>
     */
    private ArrayObject $overwritten; // @phpstan-ignore-line

    /** @param ArrayObject<int, string> $overwritten */
    public function __construct(ArrayObject $overwritten)
    {
        $this->overwritten = $overwritten;
    }

    public function __invoke(string $fileName, string $content): void
    {
        if (file_exists($fileName)) {
            /** @psalm-suppress NullArgument */
            $this->overwritten[] = $fileName;
        }

        file_put_contents($fileName, $content);
    }
}
