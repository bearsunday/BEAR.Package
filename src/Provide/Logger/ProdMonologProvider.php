<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Logger;

use BEAR\AppMeta\AbstractAppMeta;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Logger;
use Ray\Di\ProviderInterface;

class ProdMonologProvider implements ProviderInterface
{
    /**
     * @var AbstractAppMeta
     */
    private $appMeta;

    public function __construct(AbstractAppMeta $appMeta)
    {
        $this->appMeta = $appMeta;
    }

    public function get() : Logger
    {
        return new Logger($this->appMeta->name, [new ErrorLogHandler]);
    }
}
