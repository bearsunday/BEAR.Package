<?php

declare(strict_types=1);

namespace BEAR\Package\Module\Import;

use ReflectionClass;

use function assert;
use function dirname;
use function sprintf;

final class ImportApp
{
    /**
     * @param non-empty-string      $host
     * @param non-empty-string      $appName
     * @param non-empty-string      $context
     * @param non-empty-string|null $writeDir writable base of the imported app; defaults to {appDir}/var
     */
    public function __construct(
        public string $host,
        public string $appName,
        public string $context,
        public string|null $writeDir = null,
    ) {
    }

    /**
     * Directory of the imported application, resolved from its AppModule on each call.
     *
     * Never stored: the compiled container holds this object, and an artifact that moves the
     * application - a phar, an image - moves its directory. The write directory is stored
     * because it has to be the same at the build and at the boot.
     *
     * @return non-empty-string
     */
    public function appDir(): string
    {
        /** @var class-string $appModuleClass */
        $appModuleClass = sprintf('%s\\Module\\AppModule', $this->appName);
        $appModuleFile = (string) (new ReflectionClass($appModuleClass))->getFileName();
        $appDir = dirname($appModuleFile, 3);
        assert($appDir !== '');

        return $appDir;
    }
}
