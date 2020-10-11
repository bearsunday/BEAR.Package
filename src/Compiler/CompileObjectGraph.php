<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use Ray\Di\AbstractModule;
use Ray\ObjectGrapher\ObjectGrapher;

use function sprintf;

final class CompileObjectGraph
{
    /** @var FilePutContents */
    private $filePutContents;

    /** @var string */
    private $appDir;

    public function __construct(FilePutContents $filePutContents, string $appDir)
    {
        $this->filePutContents = $filePutContents;
        $this->appDir = $appDir;
    }

    public function __invoke(AbstractModule $module): string
    {
        $dotFile = sprintf('%s/module.dot', $this->appDir);
        ($this->filePutContents)($dotFile, (new ObjectGrapher())($module));

        return $dotFile;
    }
}
