<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\Router;

use BEAR\Sunday\Extension\Router\RouterInterface;
use Ray\Di\Exception\NotFound;

class RouterCollection implements RouterInterface
{
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

        throw new NotFound($globals['_SERVER']['REQUEST_URI']);
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
