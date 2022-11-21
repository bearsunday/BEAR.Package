<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Error;

use BEAR\Resource\ResourceObject;
use BEAR\Sunday\Extension\Error\ErrorInterface;
use BEAR\Sunday\Extension\Router\RouterMatch as Request;
use BEAR\Sunday\Extension\Transfer\TransferInterface;
use Exception;

/**
 * vnd.error for BEAR.Package
 *
 * @see https://github.com/blongden/vnd.error
 */
final class ErrorHandler implements ErrorInterface
{
    private ResourceObject|null $errorPage = null;

    public function __construct(
        private TransferInterface $responder,
        private ErrorLogger $logger,
        private ErrorPageFactoryInterface $factory,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Exception $e, Request $request) // phpcs:ignore SlevomatCodingStandard.Exceptions.ReferenceThrowableOnly.ReferencedGeneralException
    {
        $this->logger->__invoke($e, $request);
        $this->errorPage = $this->factory->newInstance($e, $request);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function transfer(): void
    {
        $this->responder->__invoke($this->errorPage ?? new NullPage(), []);
    }
}
