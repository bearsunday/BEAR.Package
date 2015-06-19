<?php
/**
 * This file is part of the BEAR.Package package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
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
     * @param RouterInterface    $router
     * @param WebRouterInterface $webRouter
     *
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
    public function get()
    {
        return new RouterCollection([$this->primaryRouter, $this->webRouter]);
    }
}
