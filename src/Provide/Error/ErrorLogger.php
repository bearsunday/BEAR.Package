<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Error;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\Sunday\Extension\Router\RouterMatch;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Throwable;

use function sprintf;

final class ErrorLogger
{
    public function __construct(
        private LoggerInterface $logger,
        private AbstractAppMeta $appMeta,
    ) {
    }

    public function __invoke(Throwable $e, RouterMatch $request): string
    {
        $level = $e->getCode() >= 500 ? Logger::ERROR : Logger::DEBUG;
        $logRef = new LogRef($e);
        $message = sprintf('req:"%s" code:%s e:%s(%s) logref:%s', (string) $request, $e->getCode(), $e::class, $e->getMessage(), (string) $logRef);
        $this->logger->log($level, $message);
        $logRef->log($e, $request, $this->appMeta);

        return (string) $logRef;
    }
}
