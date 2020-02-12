<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Router;

use BEAR\Sunday\Extension\Router\RouterInterface;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
use Ray\Di\ProviderInterface;

class RouterCollectionProvider implements ProviderInterface
{
    /**
     * @var RouterInterface
     */
    private $primaryRouter;

    /**
     * @var WebRouterInterface
     */
    private $webRouter;

    /**
     * @Inject
     * @Named("router=primary_router")
     */
    public function __construct(RouterInterface $router, WebRouterInterface $webRouter)
    {
        $this->primaryRouter = $router;
        $this->webRouter = $webRouter;
    }

    /**
     * {@inheritdoc}
     */
    public function get() : RouterCollection
    {
        return new RouterCollection([$this->primaryRouter, $this->webRouter]);
    }
}
