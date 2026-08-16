<?php

declare(strict_types=1);

namespace BEAR\Package\Module\Import;

use BEAR\Package\Types;
use ReflectionClass;

use function assert;
use function dirname;
use function sprintf;

/**
 * @psalm-import-type AppName from Types
 * @psalm-import-type Context from Types
 * @psalm-import-type AppDir from Types
 * @psalm-import-type WriteDir from Types
 */
final class ImportApp
{
    /**
     * @param non-empty-string $host
     * @param AppName          $appName
     * @param Context          $context
     * @param WriteDir|null    $writeDir writable base of the imported app; defaults to {appDir}/var
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
     * Never stored: the compiled container holds this object, and an artifact that moves
     * the application moves its directory. $writeDir is stored - it must not move.
     *
     * @return AppDir
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
