<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\Router;

use BEAR\Package\AbstractAppMeta;
use BEAR\Package\AppMeta;
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
     * @param RouterInterface $router
     * @param AppMeta         $appMeta
     *
     * @Inject
     * @Named("router=primary_router")
     */
    public function __construct(RouterInterface $router, AbstractAppMeta $appMeta)
    {
        $this->primaryRouter = $router;
        $routeFile = $appMeta->appDir . '/var/conf/route.php';
        if (file_exists($routeFile)) {
            include $routeFile;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        return new RouterCollection([$this->primaryRouter, new WebRouter]);
    }
}
