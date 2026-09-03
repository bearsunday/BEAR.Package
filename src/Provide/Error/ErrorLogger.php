<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Error;

use BEAR\Sunday\Extension\Router\RouterMatch;
use Psr\Log\LoggerInterface;
use Throwable;

use function sprintf;

final class ErrorLogger
{
    public function __construct(
        private LoggerInterface $logger,
        private LogRefWriterInterface $logRefWriter,
    ) {
    }

    /**
     * Log at the level the error page reports: Status decides 4xx or 5xx for both
     *
     * Explicit debug()/error() calls because monolog has different log level
     * constants(200,400) than psr/logger, and those constants change from version to version.
     */
    public function __invoke(Throwable $e, RouterMatch $request): void
    {
        $detail = (string) new ExceptionAsString($e, $request);
        $summary = sprintf('req:"%s" code:%s e:%s(%s)', (string) $request, $e->getCode(), $e::class, $e->getMessage());
        if ((new Status($e))->code < 500) {
            $this->logger->debug($summary);
            $this->logger->debug($detail);

            return;
        }

        $logRef = new LogRef($e);
        $this->logRefWriter->write($logRef, $detail);
        $this->logger->error(sprintf('%s logref:%s', $summary, (string) $logRef));
        $this->logger->error($detail);
    }
}
