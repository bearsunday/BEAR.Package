<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Logger;

use BEAR\AppMeta\AbstractAppMeta;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Ray\Di\ProviderInterface;

class MonologProvider implements ProviderInterface
{
    /**
     * @var AbstractAppMeta
     */
    private $appMeta;

    public function __construct(AbstractAppMeta $appMeta)
    {
        $this->appMeta = $appMeta;
    }

    /**
     * {@inheritdoc}
     */
    public function get() : Logger
    {
        $format = "[%datetime%] %level_name%: %message% %context%\n";
        $stream = new StreamHandler($this->appMeta->logDir . '/app.log', Logger::DEBUG);
        $stream->setFormatter(new LineFormatter($format));

        return new Logger($this->appMeta->name, [$stream]);
    }
}
