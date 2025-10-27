<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Logger;

use BEAR\AppMeta\AbstractAppMeta;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Logger;
use Override;
use Ray\Di\ProviderInterface;

/** @implements ProviderInterface<Logger> */
final class ProdMonologProvider implements ProviderInterface
{
    public function __construct(
        private AbstractAppMeta $appMeta,
    ) {
    }

    #[Override]
    public function get(): Logger
    {
        return new Logger($this->appMeta->name, [new ErrorLogHandler()]);
    }
}
