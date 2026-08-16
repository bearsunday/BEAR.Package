<?php

declare(strict_types=1);

namespace BEAR\Package\Module\Import;

use BEAR\AppMeta\Exception\AppNameException;
use BEAR\AppMeta\Meta;
use BEAR\Package\Types;

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
     * the application moves its directory.
     *
     * @return AppDir
     *
     * @throws AppNameException
     */
    public function appDir(): string
    {
        return Meta::appDir($this->appName);
    }
}
