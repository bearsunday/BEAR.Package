<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Logger;

use BEAR\AppMeta\AbstractAppMeta;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Logger;
use Ray\Di\ProviderInterface;

/** @implements ProviderInterface<Logger> */
class ProdMonologProvider implements ProviderInterface
{
    public function __construct(
        private AbstractAppMeta $appMeta,
    ) {
    }

    public function get(): Logger
    {
        return new Logger($this->appMeta->name, [new ErrorLogHandler()]);
    }
}
