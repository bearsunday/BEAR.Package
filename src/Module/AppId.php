<?php

declare(strict_types=1);

namespace BEAR\Package\Module;

use BEAR\Package\Types;

/**
 * @psalm-import-type AppName from Types
 * @psalm-import-type Context from Types
 */
final class AppId
{
    /**
     * @param AppName $name
     * @param Context $context
     */
    public function __construct(
        public readonly string $name,
        public readonly string $context,
    ) {
    }
}
