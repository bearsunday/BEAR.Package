<?php

declare(strict_types=1);

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
     * @param array{REQUEST_METHOD: string, HTTP_X_HTTP_METHOD_OVERRIDE?: string, ...} $server $_SERVER
     * @param array<string, mixed>                                                $get  $_GET
     * @param array<string, mixed>                                                $post $_POST
     *
     * @return array{0: string, 1: array<string, mixed>}
     */
    // phpcs:ignore Squiz.Commenting.FunctionComment.MissingParamName
    public function get(array $server, array $get, array $post);
}
