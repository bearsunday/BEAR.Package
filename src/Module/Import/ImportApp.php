<?php

declare(strict_types=1);

namespace BEAR\Package\Module\Import;

use ReflectionClass;

use function assert;
use function class_exists;
use function dirname;
use function sprintf;

final class ImportApp
{
    public string $host;
    public string $appName;
    public string $context;
    public string $appDir;

    public function __construct(string $host, string $appName, string $context)
    {
        $this->host = $host;
        $this->appName = $appName;
        $this->context = $context;
        $appModuleClass = sprintf('%s\\Module\\AppModule', $this->appName);
        assert(class_exists($appModuleClass));
        $this->appDir = dirname((new ReflectionClass($appModuleClass))->getFileName(), 3);
    }
}
