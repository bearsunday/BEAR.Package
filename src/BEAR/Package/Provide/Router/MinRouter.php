<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\Router;

use Aura\Router\Map;
use BEAR\Sunday\Extension\Router\RouterInterface;
use BEAR\Resource\Exception\BadRequest;
use BEAR\Resource\Exception\MethodNotAllowed;
use Ray\Di\Di\Inject;

/**
 * Standard min router
 *
 * The constructor can accepts "Aura.Route" routing
 * @see https://github.com/auraphp/Aura.Router
 *
 * @package    BEAR.Package
 * @subpackage Route
 */
final class MinRouter implements RouterInterface
{
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

    const METHOD_OVERRIDE = 'X-HTTP-Method-Override';
    const METHOD_OVERRIDE_GET = '_method';

    /**
     * Constructor
     *
     * @param Map $map
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
        $this->globals = $this->globals ?: $GLOBALS;
        $globals = $this->globals;
        $uri = $globals['_SERVER']['REQUEST_URI'];
        $route = $this->map ? $this->map->match(parse_url($uri, PHP_URL_PATH), $globals['_SERVER']) : false;
        if ($route === false) {
            list($method, $query,) = $this->getMethodQuery();
            $pageUri = $this->getPageKey();
        } else {
            $method = $route->values['action'];
            $pageUri = $route->values['page'];
            $query = [];
            $keys = array_keys($route->params);
            foreach ($keys as $key) {
                $query[$key] = $route->values[$key];
            }
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
            $method = $globals['_GET'][self::METHOD_OVERRIDE_GET];
            $query = $globals['_GET'];
        } elseif ($globals['_SERVER']['REQUEST_METHOD'] === 'POST' && isset($globals['_POST'][self::METHOD_OVERRIDE])) {
            $method = $globals['_POST'][self::METHOD_OVERRIDE];
            $query = $globals['_POST'];
        } else {
            $method = $globals['_SERVER']['REQUEST_METHOD'];
            $query = $globals['_GET'];
        }

        $method = strtolower($method);

        return [$method, $query];
    }

    /**
     * Return page key
     *
     * @return array [$method, $pagekey]
     * @throws \InvalidArgumentException
     */
    private function getPageKey()
    {
        if (!isset($this->globals['_SERVER']['REQUEST_URI'])) {
            return '404';
        }
        $pageKey = substr($this->globals['_SERVER']['REQUEST_URI'], 1);

        return $pageKey;
    }
}
