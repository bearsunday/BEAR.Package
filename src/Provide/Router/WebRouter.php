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

class WebRouter implements RouterInterface, WebRouterInterface
{
    /**
     * @var string
     */
    private $defaultRouteUri;

    /**
     * @Inject
     * @Named("default_route_uri")
     */
    public function __construct($default_route_uri = 'page://self')
    {
        $this->defaultRouteUri = $default_route_uri;
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
            $this->defaultRouteUri . parse_url($server['REQUEST_URI'], PHP_URL_PATH),
            ($method === 'get') ? $globals['_GET'] : $globals['_POST']
        ];

        return $request;
    }

    /**
     * {@inheritdoc}
     */
    public function generate($name, $data)
    {
        return false;
    }
}
