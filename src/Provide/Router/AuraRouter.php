<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\Router;

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
    private $defaultScheme = 'page://self';

    /**
     * @param Router $router
     *
     * @Inject
     * @Named("original")
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * Default route host
     *
     * @var string
     */
    private $defaultRouteHost = 'page://self';

    /**
     * @param $defaultScheme
     *
     * @Inject(optional=true)
     * @Named("default_route_host")
     */
    public function setDefaultScheme($defaultRoutHost)
    {
        $this->defaultRouteHost = $defaultRoutHost;
    }

    /**
     * {@inheritdoc}
     */
    public function match(array $globals, array $server)
    {
        $urlPath = parse_url($server['REQUEST_URI'], PHP_URL_PATH);
        $route = $this->router->match($urlPath, $server);
        if ($route === false) {
            return false;
        }
        $request = new RouterMatch;
        $params = $route->params;
        // path
        $path = substr($params['path'], 0, 1) === '/' ? $this->defaultRouteHost . $params['path'] : $params['path'];
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
