<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Router;

use BEAR\Sunday\Extension\Router\RouterInterface;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
use Ray\Di\ProviderInterface;

/** @implements ProviderInterface<RouterCollection> */
class RouterCollectionProvider implements ProviderInterface
{
    private RouterInterface $primaryRouter;

    /**
     * @Inject
     * @Named("router=primary_router")
     */
    #[Inject, Named('router=primary_router')]
    public function __construct(
        RouterInterface $router,
        private WebRouterInterface $webRouter,
    ) {
        $this->primaryRouter = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function get(): RouterCollection
    {
        return new RouterCollection([$this->primaryRouter, $this->webRouter]);
    }
}
