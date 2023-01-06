<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Router;

use BEAR\Sunday\Extension\Router\RouterInterface;
use Ray\Di\Di\Named;
use Ray\Di\ProviderInterface;

/** @implements ProviderInterface<RouterCollection> */
class RouterCollectionProvider implements ProviderInterface
{
    public function __construct(
        #[Named('primary_router')]
        private RouterInterface $primaryRouter,
        private WebRouterInterface $webRouter,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function get(): RouterCollection
    {
        return new RouterCollection([$this->primaryRouter, $this->webRouter]);
    }
}
