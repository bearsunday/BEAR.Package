<?php

declare(strict_types=1);

namespace FakeVendor\HelloWorld\Module;

use BEAR\Resource\ResourceInterface;
use BEAR\Sunday\Extension\Application\AbstractApp;
use BEAR\Sunday\Extension\Error\ErrorInterface;
use BEAR\Sunday\Extension\Router\RouterInterface;
use BEAR\Sunday\Extension\Transfer\HttpCacheInterface;
use BEAR\Sunday\Extension\Transfer\TransferInterface;

class App extends AbstractApp
{
    public static $construct = 0;

    public function __construct(HttpCacheInterface $httpCache, RouterInterface $router, TransferInterface $responder, ResourceInterface $resource, ErrorInterface $error)
    {
        parent::__construct($httpCache, $router, $responder, $resource, $error);
        self::$construct++;
    }
}
