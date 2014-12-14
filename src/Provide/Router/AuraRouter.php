<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\Router;

use Aura\Router\Router;
use Aura\Web\Request\Method;
use BEAR\Sunday\Extension\Router\RouterMatch;
use BEAR\Sunday\Extension\Router\RouterInterface;

class AuraRouter implements RouterInterface
{
    const METHOD_FILED = '_method';

    const METHOD_OVERRIDE_HEADER = 'HTTP_X_HTTP_METHOD_OVERRIDE';

    /**
     * @var Router
     */
    private $router;

    /**
     * @param Router $router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function match(array $globals = [])
    {
        $urlPath = parse_url($globals['_SERVER']['REQUEST_URI'], PHP_URL_PATH);
        $route = $this->router->match($urlPath, $globals['_SERVER']);
        if ($route === false) {
            return false;
        }
        $request = new RouterMatch;
        $params = $route->params;
        // path
        $request->path = $params['path'];
        // query
        unset($params['path']);
        $params += ($globals['_SERVER']['REQUEST_METHOD'] === 'GET') ? $globals['_GET'] : $globals['_POST'];
        unset($params[self::METHOD_FILED]);
        $request->query = $params;
        // method
        $request->method = strtolower((new Method($globals['_SERVER'], $globals['_POST'], self::METHOD_FILED))->get());

        return $request;
    }
}
