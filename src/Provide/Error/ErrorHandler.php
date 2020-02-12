<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Error;

use BEAR\Resource\ResourceObject;
use BEAR\Sunday\Extension\Error\ErrorInterface;
use BEAR\Sunday\Extension\Router\RouterMatch as Request;
use BEAR\Sunday\Extension\Transfer\TransferInterface;

/**
 * vnd.error for BEAR.Package
 *
 * @see https://github.com/blongden/vnd.error
 */
final class ErrorHandler implements ErrorInterface
{
    /**
     * @var ResourceObject
     */
    private $errorPage;

    /**
     * @var TransferInterface
     */
    private $responder;

    /**
     * @var ErrorLogger
     */
    private $logger;

    /**
     * @var ErrorPageFactoryInterface
     */
    private $factory;

    public function __construct(TransferInterface $responder, ErrorLogger $logger, ErrorPageFactoryInterface $factory)
    {
        $this->responder = $responder;
        $this->logger = $logger;
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(\Exception $e, Request $request)
    {
        $this->logger->__invoke($e, $request);
        $this->errorPage = $this->factory->newInstance($e, $request);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function transfer() : void
    {
        $this->responder->__invoke($this->errorPage, []);
    }
}
