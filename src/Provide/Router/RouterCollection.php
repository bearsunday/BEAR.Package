<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\Router;

use BEAR\Sunday\Extension\Router\RouterInterface;
use BEAR\Sunday\Extension\Router\RouterMatch;
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
     * @param array  $globals
     *
     * @return RouterMatch
     */
    public function match(array $globals)
    {
        foreach ($this->routers as $route) {
            $match = $route->match($globals);
            if ($match !== false) {
                return $match;
            }
        }

        throw new NotFound($globals['_SERVER']['REQUEST_URI']);
    }
}
