<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Error;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\Sunday\Extension\Router\RouterMatch;
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
        $isError = $e->getCode() >= 500;
        $logRef = new LogRef($e);
        $logRef->log($e, $request, $this->appMeta);
        $message = sprintf('req:"%s" code:%s e:%s(%s) logref:%s', (string) $request, $e->getCode(), $e::class, $e->getMessage(), (string) $logRef);
        $this->log($isError, $message);

        return (string) $logRef;
    }

    /**
     * Log with method
     *
     * monolog has different log level constants(200,400) than psr/logger,
     * and those constants change from version to version.
     */
    private function log(bool $isError, string $message): void
    {
        if ($isError) {
            $this->logger->error($message);

            return;
        }

        $this->logger->debug($message);
    }
}
