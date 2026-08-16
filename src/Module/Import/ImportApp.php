<?php

declare(strict_types=1);

namespace BEAR\Package\Module\Import;

use BEAR\AppMeta\Exception\AppNameException;
use BEAR\Package\Types;
use ReflectionClass;

use function assert;
use function class_exists;
use function dirname;
use function sprintf;

/**
 * @psalm-import-type AppName from Types
 * @psalm-import-type Context from Types
 * @psalm-import-type AppDir from Types
 */
final class ImportApp
{
    /**
     * @param non-empty-string $host
     * @param AppName          $appName
     * @param Context          $context
     */
    public function __construct(
        public string $host,
        public string $appName,
        public string $context,
    ) {
    }

    /**
     * Directory of the imported application, resolved from its AppModule on each call.
     *
     * Never stored: the compiled container holds this object, and an artifact that moves
     * the application moves its directory. $writeDir is stored - it must not move.
     *
     * Resolved the way Meta resolves its own, down to the exception an unknown name gets.
     *
     * @return AppDir
     *
     * @throws AppNameException
     */
    public function appDir(): string
    {
        $appModuleClass = sprintf('%s\\Module\\AppModule', $this->appName);
        if (! class_exists($appModuleClass)) {
            throw new AppNameException($this->appName);
        }

        $appModuleFile = (new ReflectionClass($appModuleClass))->getFileName();
        assert($appModuleFile !== false);

        return dirname($appModuleFile, 3);
    }
}
