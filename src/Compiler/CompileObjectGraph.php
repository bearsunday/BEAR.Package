<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use Ray\Di\AbstractModule;
use Ray\ObjectGrapher\ObjectGrapher;

use function sprintf;

final class CompileObjectGraph
{
    public function __construct(
        private FilePutContents $filePutContents,
        private string $dotDir,
        private DotCommand $dotCommand,
    ) {
    }

    public function __invoke(AbstractModule $module): string
    {
        $dotFile = sprintf('%s/module.dot', $this->dotDir);
        ($this->filePutContents)($dotFile, (new ObjectGrapher())($module));
        $svgFile = sprintf('%s/module.svg', $this->dotDir);

        return ($this->dotCommand)($dotFile, $svgFile) ? $svgFile : $dotFile;
    }
}
