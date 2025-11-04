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
    /** @var non-empty-string */
    public string $appDir;

    /**
     * @param non-empty-string $host
     * @param non-empty-string $appName
     * @param non-empty-string $context
     */
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
        assert($appDir !== '');
        $this->appDir = $appDir;
    }
}
