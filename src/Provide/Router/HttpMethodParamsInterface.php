<?php
/**
 * This file is part of the BEAR.Sunday package
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Package\Provide\Router;

interface HttpMethodParamsInterface
{
    /**
     * Return http method and parameters
     *
     * 'parameters' change by method.
     * get method return $_GET, post method return $_POST
     * patch | put | delete  return parsed 'php://input' value if form-urlencoded or json content
     *
     * @param array $server $_SERVER
     * @param array $get    $_GET
     * @param array $post   $_POST
     *
     * @return array [$method, $params]
     */
    public function get(array $server, array $get, array $post);
}
