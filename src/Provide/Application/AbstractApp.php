<?php

declare(strict_types=1);
/**
 * This file is part of the BEAR.Sunday package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Pacakge\Extension\Application;

use BEAR\QueryRepository\HttpCacheInterface;
use BEAR\Resource\ResourceInterface;
use BEAR\Sunday\Extension\Application\AppInterface;
use BEAR\Sunday\Extension\Error\ErrorInterface;
use BEAR\Sunday\Extension\Router\RouterInterface;
use BEAR\Sunday\Extension\Transfer\TransferInterface;

class AbstractApp implements AppInterface
{
    /**
     * @var HttpCacheInterface
     */
    public $httpCache;

    /**
     * @var RouterInterface
     */
    public $router;

    /**
     * @var TransferInterface
     */
    public $responder;

    /**
     * @var ResourceInterface
     */
    public $resource;

    /**
     * @var ErrorInterface
     */
    public $error;

    /**
     * @param HttpCacheInterface $httpCache HTTP Cache
     * @param RouterInterface    $router    Resource router
     * @param TransferInterface  $responder Resource responder
     * @param ResourceInterface  $resource  BEAR.Resource client
     * @param ErrorInterface     $error     Error handler
     */
    public function __construct(
        HttpCacheInterface $httpCache,
        RouterInterface $router,
        TransferInterface $responder,
        ResourceInterface $resource,
        ErrorInterface $error
    ) {
        $this->httpCache = $httpCache;
        $this->router = $router;
        $this->responder = $responder;
        $this->resource = $resource;
        $this->error = $error;
    }
}
