<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\Router\Adapter;

final class RouterCollection implements AdapterInterface
{
    /**
     * @var AdapterInterface[]
     */
    private $routers;

    /**
     * @param AdapterInterface[] $routers
     */
    public function __construct(array $routers)
    {
        $this->routers = $routers;
    }

    /**
     * @param string $path
     * @param array  $globals
     *
     * @return array [$method, $path, $query]
     */
    public function match($path, array $globals = [])
    {
        foreach ($this->routers as $route) {
            $match = $route->match($path, $globals);
            if ($match !== false) {
                return $match;
            }
        }

        return $match;
    }
}
