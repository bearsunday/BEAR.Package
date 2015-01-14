<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\Router;

use BEAR\Sunday\Extension\Router\RouterInterface;
use BEAR\Sunday\Extension\Router\RouterMatch;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

class WebRouter implements RouterInterface
{
    /**
     * @var string
     */
    private $defaultRouteHost  = 'page://self';

    /**
     * @Named("default_route_host")
     * @Inject(optional=true)
     */
    public function setRouteHost($default_route_host)
    {
        $this->defaultRouteHost = $default_route_host;
    }

    /**
     * {@inheritdoc}
     */
    public function match(array $globals, array $server)
    {
        $request = new RouterMatch;
        $method = strtolower($server['REQUEST_METHOD']);
        list($request->method, $request->path, $request->query) = [
            $method,
            $this->defaultRouteHost . parse_url($server['REQUEST_URI'], PHP_URL_PATH),
            ($method === 'get') ? $globals['_GET'] : $globals['_POST']
        ];

        return $request;
    }
}
