<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Logger;

use BEAR\AppMeta\AbstractAppMeta;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Override;
use Ray\Di\ProviderInterface;

/** @implements ProviderInterface<Logger> */
final class MonologProvider implements ProviderInterface
{
    public function __construct(
        private AbstractAppMeta $appMeta,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function get(): Logger
    {
        $format = "[%datetime%] %level_name%: %message% %context%\n";
        $stream = new StreamHandler($this->appMeta->logDir . '/app.log');
        $stream->setFormatter(new LineFormatter($format));

        return new Logger($this->appMeta->name, [$stream]);
    }
}
