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
use Ray\Di\ProviderInterface;

class AuraRouterProvider implements ProviderInterface
{
    /**
     * @var Router
     */
    private $router;

    /**
     * @param AbstractAppMeta $appMeta
     */
    public function __construct(AbstractAppMeta $appMeta)
    {
        $routerCollection = new RouteCollection(new RouteFactory());
        $routerCollection->setResourceCallable([$this, 'resourceRoute']);
        $router = new Router(
            $routerCollection,
            new Generator
        );
        $routeFile = $appMeta->appDir . '/var/conf/aura.router.php';
        if (file_exists($routeFile)) {
            include $routeFile;
        }
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        return new AuraRouter($this->router);
    }

    /**
     * @param RouteCollection $router
     */
    public function resourceRoute(RouteCollection $router)
    {
        $router->setTokens(array(
            'id' => '([a-f0-9]+)'
        ));
        $router->addPost('post', '/{id}');
        $router->addGet('get', '/{id}');
        $router->addPut('put', '/{id}');
        $router->addPatch('patch', '/{id}');
        $router->addDelete('delete', '/{id}');
    }
}
