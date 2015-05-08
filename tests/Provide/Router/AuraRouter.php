<?php
/**
 * This file is part of the BEAR.AuraRouterModule package
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Package\Provide\Router;

use Aura\Router\Exception\RouteNotFound;
use Aura\Router\Route;
use Aura\Router\Router;
use Aura\Web\Request\Method;
use BEAR\Sunday\Annotation\DefaultSchemeHost;
use BEAR\Sunday\Extension\Router\RouterInterface;
use BEAR\Sunday\Extension\Router\RouterMatch;

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
    private $schemeHost = 'page://self';

    /**
     * @var HttpMethodParamsInterface
     */
    private $httpMethodParams;

    /**
     * @param Router                    $router
     * @param string                    $schemeHost
     * @param HttpMethodParamsInterface $httpMethodParams
     *
     * @DefaultSchemeHost("schemeHost")
     */
    public function __construct(Router $router, $schemeHost, HttpMethodParamsInterface $httpMethodParams)
    {
        $this->schemeHost = $schemeHost;
        $this->router = $router;
        $this->httpMethodParams = $httpMethodParams;
    }

    /**
     * {@inheritdoc}
     */
    public function match(array $globals, array $server)
    {
        $path = parse_url($server['REQUEST_URI'], PHP_URL_PATH);
        $route = $this->router->match($path, $server);
        if ($route === false) {
            return false;
        }
        $request = $this->getRequest($globals, $server, $route);

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

    /**
     * Return resource request
     *
     * @param array $globals
     * @param array $server
     * @param Route $route
     *
     * @return RouterMatch
     */
    private function getRequest(array $globals, array $server, Route $route)
    {
        $request = new RouterMatch;
        $params = $route->params;
        // path
        $path = substr($params['path'], 0, 1) === '/' ? $this->schemeHost . $params['path'] : $params['path'];
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
}
