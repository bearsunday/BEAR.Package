<?php
/**
 * This file is part of the BEAR.Package package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Package\Provide\Router;

use BEAR\Sunday\Extension\Router\RouterInterface;
use BEAR\Sunday\Extension\Router\RouterMatch;

class RouterCollection implements RouterInterface
{
    const ROUTE_NOT_FOUND = 'page://self/__route_not_found';
    /**
     * @var RouterInterface[]
     */
    private $routers;

    /**
     * @param RouterInterface[] $routers
     */
    public function __construct(array $routers)
    {
        $this->routers = $routers;
    }

    /**
     * {@inheritdoc}
     */
    public function match(array $globals, array $server)
    {
        foreach ($this->routers as $route) {
            $match = $route->match($globals, $server);
            if ($match !== false) {
                return $match;
            }
        }

        return $this->routeNotFound();
    }

    /**
     * @return RouterMatch
     */
    private function routeNotFound()
    {
        $routeMatch = new RouterMatch;
        $routeMatch->method = 'get';
        $routeMatch->path = self::ROUTE_NOT_FOUND;

        return $routeMatch;
    }
    /**
     * {@inheritdoc}
     */
    public function generate($name, $data)
    {
        foreach ($this->routers as $route) {
            $uri = $route->generate($name, $data);
            if ($uri) {
                return $uri;
            }
        }

        return false;
    }
}
