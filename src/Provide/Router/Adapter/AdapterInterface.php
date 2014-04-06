<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */

namespace BEAR\Package\Provide\Router\Adapter;

interface AdapterInterface
{
    /**
     * @param string $path
     * @param array  $globals
     *
     * @return array array [$method, $requestUri, $query]
     */
    public function match($path, array $globals = []);
}
