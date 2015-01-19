<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\Router;

use Aura\Router\Generator;
use Aura\Router\RouteCollection;
use Aura\Router\RouteFactory;
use Aura\Router\Router;
use BEAR\Package\AbstractAppMeta;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
use Ray\Di\ProviderInterface;

class AuraRouterProvider implements ProviderInterface
{
    /**
     * @var Router
     */
    private $router;

    private $defaultRouteUri;
    /**
     * @Inject
     * @Named("defaultRouteUri=default_route_uri")
     */
    public function __construct(AbstractAppMeta $appMeta, $defaultRouteUri = 'page://self')
    {
        $this->defaultRouteUri = $defaultRouteUri;
        $router = new Router(new RouteCollection(new RouteFactory), new Generator);
        $routeFile = $appMeta->appDir . '/var/conf/aura.route.php';
        include $routeFile;
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        return new AuraRouter($this->router, $this->defaultRouteUri);
    }
}
