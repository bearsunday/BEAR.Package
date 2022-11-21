<?php

declare(strict_types=1);

namespace BEAR\Package\Module\Import;

use ReflectionClass;

use function assert;
use function dirname;
use function is_dir;
use function sprintf;

final class ImportApp
{
    public string $appDir;

    public function __construct(
        public string $host,
        public string $appName,
        public string $context,
    ) {
        /** @var class-string $appModuleClass */
        $appModuleClass = sprintf('%s\\Module\\AppModule', $this->appName);
        $appModuleClassName = (string) (new ReflectionClass($appModuleClass))->getFileName();
        $appDir = dirname($appModuleClassName, 3);
        assert(is_dir($appDir));
        $this->appDir = $appDir;
    }
}
