<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use function file_exists;
use function file_put_contents;

class FilePutContents
{
    /** @var list<string> */
    private $overwritten = [];

    public function __invoke(string $fileName, string $content): void
    {
        if (file_exists($fileName)) {
            $this->overwritten[] = $fileName;
        }

        file_put_contents($fileName, $content);
    }
}
