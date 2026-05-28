<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Router;

use BEAR\Package\Types;

/**
 * @psalm-import-type ServerArray from Types
 * @psalm-import-type QueryParams from Types
 * @psalm-type HttpServer = array{REQUEST_METHOD: string, HTTP_X_HTTP_METHOD_OVERRIDE?: mixed, CONTENT_TYPE?: mixed, HTTP_CONTENT_TYPE?: mixed, HTTP_RAW_POST_DATA?: mixed, ...}
 * @phpstan-type HttpServer array{REQUEST_METHOD: string, HTTP_X_HTTP_METHOD_OVERRIDE?: mixed, CONTENT_TYPE?: mixed, HTTP_CONTENT_TYPE?: mixed, HTTP_RAW_POST_DATA?: mixed, ...<string, mixed>}
 */
interface HttpMethodParamsInterface
{
    /**
     * Return http method and parameters
     *
     * 'parameters' change by method.
     * get method return $_GET, post method return $_POST
     * patch | put | delete  return parsed 'php://input' value if form-urlencoded or json content
     *
     * @param HttpServer $server $_SERVER
     * @param QueryParams                                                $get  $_GET
     * @param QueryParams                                                $post $_POST
     *
     * @return array{0: string, 1: QueryParams}
     */
    // phpcs:ignore Squiz.Commenting.FunctionComment.MissingParamName
    public function get(array $server, array $get, array $post);
}
