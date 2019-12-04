<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Router;

use BEAR\Sunday\Extension\Router\NullMatch;
use BEAR\Sunday\Extension\Router\RouterInterface;
use BEAR\Sunday\Extension\Router\RouterMatch;

class RouterCollection implements RouterInterface
{
    private const ROUTE_NOT_FOUND = 'page://self/__route_not_found';

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
            if ($match instanceof RouterMatch && ! ($match instanceof NullMatch)) {
                return $match;
            }
        }

        return $this->routeNotFound();
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

    private function routeNotFound() : RouterMatch
    {
        $routeMatch = new RouterMatch;
        $routeMatch->method = 'get';
        $routeMatch->path = self::ROUTE_NOT_FOUND;

        return $routeMatch;
    }
}
