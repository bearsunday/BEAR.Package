<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\Router;

/**
 * Map interface
 */
interface MapInterface
{
    /**
     * Gets a route that matches a given path and other server conditions.
     *
     * @param string $path The path to match against.
     *
     * @param array $server An array copy of $_SERVER.
     *
     * @return Route|false Returns a Route object when it finds a match, or
     * boolean false if there is no match.
     *
     */
    public function match($path, array $server);
}
