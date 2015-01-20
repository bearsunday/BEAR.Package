<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\Router;

use Aura\Router\Exception\RouteNotFound;
use Aura\Router\Router;
use Aura\Web\Request\Method;
use BEAR\Sunday\Extension\Router\RouterInterface;
use BEAR\Sunday\Extension\Router\RouterMatch;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

class AuraRouter implements RouterInterface
{
    const METHOD_FILED = '_method';

    const METHOD_OVERRIDE_HEADER = 'HTTP_X_HTTP_METHOD_OVERRIDE';

    /**
     * @var Router
     */
    private $router;

    /**
     * @var string
     */
    private $defaultRouteUri;

    /**
     * @param Router $router          Aura Router
     * @param string $defaultRouteUri default scheme+host
     */
    public function __construct(Router $router, $defaultRouteUri = 'page://self')
    {
        $this->router = $router;
        $this->defaultRouteUri = $defaultRouteUri;
    }

    /**
     * {@inheritdoc}
     */
    public function match(array $globals, array $server)
    {
        $route = $this->router->match( parse_url($server['REQUEST_URI'], PHP_URL_PATH), $server);
        if ($route === false) {
            return false;
        }
        $request = new RouterMatch;
        $params = $route->params;
        // path
        $path = substr($params['path'], 0, 1) === '/' ? $this->defaultRouteUri . $params['path'] : $params['path'];
        $request->path = $path;
        // query
        unset($params['path']);
        $params += ($server['REQUEST_METHOD'] === 'GET') ? $globals['_GET'] : $globals['_POST'];
        unset($params[self::METHOD_FILED]);
        $request->query = $params;
        // method
        $request->method = strtolower((new Method($server, $globals['_POST'], self::METHOD_FILED))->get());

        return $request;
    }

    /**
     * {@inheritdoc}
     */
    public function generate($name, $data)
    {
        try {
            return $this->router->generate($name, $data);
        } catch (RouteNotFound $e) {
            return false;
        }
    }
}
