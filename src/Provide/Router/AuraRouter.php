<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\Router;

use Aura\Router\Map;
use BEAR\Resource\Exception\BadRequest;
use BEAR\Resource\Exception\MethodNotAllowed;
use BEAR\Sunday\Extension\Router\RouterInterface;
use Ray\Di\Di\Inject;
use Aura\Router\Route as AuraRoute;

/**
 * Standard router
 *
 * This router accepts Aura.Router optionally.
 *
 * @see https://github.com/auraphp/Aura.Router
 */
class AuraRouter implements RouterInterface
{
    const METHOD_OVERRIDE = 'X-HTTP-Method-Override';

    const METHOD_OVERRIDE_GET = '_method';

    /**
     * $GLOBALS
     *
     * @var array
     */
    private $globals;

    /**
     * map
     *
     * @var \Aura\Router\Map;
     */
    private $map;

    /**
     * @param Map $map
     *
     * @Inject(optional=true)
     */
    public function __construct(Map $map = null)
    {
        $this->map = $map;
    }

    /**
     * {@inheritDoc}
     */
    public function setGlobals($global)
    {
        $this->globals = $global;
        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * @throws BadRequest
     * @throws MethodNotAllowed
     */
    public function setArgv($argv)
    {
        if (count($argv) < 3) {
            throw new BadRequest('Usage: [get|post|put|delete] [uri]');
        }
        $globals['_SERVER']['REQUEST_METHOD'] = $argv[1];
        $globals['_SERVER']['REQUEST_URI'] = parse_url($argv[2], PHP_URL_PATH);
        parse_str(parse_url($argv[2], PHP_URL_QUERY), $get);
        $globals['_GET'] = $get;
        $this->globals = $globals;

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * @return array [$method, $pageUri, $query]
     */
    public function match()
    {
        $this->globals = $this->globals ? : $GLOBALS;
        $globals = $this->globals;
        $uri = $globals['_SERVER']['REQUEST_URI'];
        $route = $this->map ? $this->map->match(parse_url($uri, PHP_URL_PATH), $globals['_SERVER']) : false;

        if ($route !== false) {
            return $this->hasRoute($route);
        }

        list($method, $query,) = $this->getMethodQuery();
        $method = strtolower($method);
        $pageUri = $this->getPageKey();
        unset($query[self::METHOD_OVERRIDE]);

        return [$method, $pageUri, $query];
    }

    /**
     * @param AuraRoute $route
     *
     * @return array
     */
    private function hasRoute(AuraRoute $route)
    {
        list($method, $query) = $this->getMethodQuery();

        $method = isset($route->values['method']) ? $route->values['method'] : $method;

        $pageUri = $route->values['path'];
        $keys = array_keys($route->params);
        foreach ($keys as $key) {
            $query[$key] = $route->values[$key];
        }
        unset($query[self::METHOD_OVERRIDE]);

        return [$method, $pageUri, $query];
    }

    /**
     * Return request method
     *
     * @return array [$method, $query]
     */
    private function getMethodQuery()
    {
        $globals = $this->globals;

        if ($globals['_SERVER']['REQUEST_METHOD'] === 'GET' && isset($globals['_GET'][self::METHOD_OVERRIDE_GET])) {
            return [
                strtolower($globals['_GET'][self::METHOD_OVERRIDE_GET]),
                $globals['_GET']
            ];
        } elseif ($globals['_SERVER']['REQUEST_METHOD'] === 'POST' && isset($globals['_POST'][self::METHOD_OVERRIDE])) {
            return [
                strtolower($globals['_POST'][self::METHOD_OVERRIDE]),
                $globals['_POST']
            ];
        }
        return [
            strtolower($globals['_SERVER']['REQUEST_METHOD']),
            $globals['_GET']
        ];
    }

    /**
     * Return page key
     *
     * @return array [$method, $pagekey]
     */
    private function getPageKey()
    {
        $pageKey = substr($this->globals['_SERVER']['REQUEST_URI'], 1);

        return $pageKey;
    }
}
