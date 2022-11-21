<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use Ray\Di\AbstractModule;
use Ray\ObjectGrapher\ObjectGrapher;

use function passthru;
use function sprintf;
use function str_replace;

final class CompileObjectGraph
{
    public function __construct(
        private FilePutContents $filePutContents,
        private string $dotDir,
    ) {
    }

    public function __invoke(AbstractModule $module): string
    {
        $dotFile = sprintf('%s/module.dot', $this->dotDir);
        ($this->filePutContents)($dotFile, (new ObjectGrapher())($module));
        $svgFile = str_replace('.dot', '.svg', $dotFile);
        $cmd = "dot -Tsvg {$dotFile} -o {$svgFile}";
        passthru('which dotsrc/Compiler/FakeRun.php 2>/dev/null', $status);
        // @codeCoverageIgnoreStart
        if ($status === 0) {
            passthru($cmd, $status);

            return $svgFile;
        }

        return $dotFile;
        // @codeCoverageIgnoreEnd
    }
}
