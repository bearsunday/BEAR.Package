<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Router;

use BEAR\Package\Annotation\StdIn;
use BEAR\Package\Exception\InvalidRequestJsonException;
use Ray\Di\Di\Inject;

final class HttpMethodParams implements HttpMethodParamsInterface
{
    const CONTENT_TYPE = 'CONTENT_TYPE';

    const HTTP_CONTENT_TYPE = 'HTTP_CONTENT_TYPE';

    const FORM_URL_ENCODE = 'application/x-www-form-urlencoded';

    const APPLICATION_JSON = 'application/json';

    /**
     * @var string
     */
    private $stdIn = 'php://input';

    /**
     * @param string $stdIn
     *
     * @Inject(optional=true)
     * @StdIn
     */
    public function setStdIn($stdIn) : void
    {
        $this->stdIn = $stdIn;
    }

    /**
     * {@inheritdoc}
     */
    public function get(array $server, array $get, array $post)
    {
        // set the original value
        $method = strtolower($server['REQUEST_METHOD']);

        // early return on GET
        if ($method === 'get') {
            return ['get', $get];
        }

        return $this->unsafeMethod($method, $server, $post);
    }

    private function unsafeMethod(string $method, array $server, array $post) : array
    {
        $params = $this->getParams($method, $server, $post);

        if ($method === 'post') {
            list($method, $params) = $this->getOverrideMethod($method, $server, $params);
        }

        return [$method, $params];
    }

    private function getOverrideMethod(string $method, array $server, array $params) : array
    {
        // must be a POST to do an override

        // look for override in post data
        if (isset($params['_method'])) {
            $method = strtolower($params['_method']);
            unset($params['_method']);

            return [$method, $params];
        }

        // look for override in headers
        if (isset($server['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
            $method = strtolower($server['HTTP_X_HTTP_METHOD_OVERRIDE']);
        }

        return [$method, $params];
    }

    /**
     * Return request parameters
     */
    private function getParams(string $method, array $server, array $post) : array
    {
        // post data exists
        if ($method === 'post' && ! empty($post)) {
            return $post;
        }

        if (\in_array($method, ['post', 'put', 'patch', 'delete'], true)) {
            return $this->phpInput($server);
        }

        return $post;
    }

    /**
     * Return request query by media-type
     */
    private function phpInput(array $server) : array
    {
        $contentType = $server[self::CONTENT_TYPE] ?? ($server[self::HTTP_CONTENT_TYPE]) ?? '';
        $isFormUrlEncoded = strpos($contentType, self::FORM_URL_ENCODE) !== false;
        if ($isFormUrlEncoded) {
            parse_str(rtrim($this->getRawBody($server)), $put);

            return $put;
        }
        $isApplicationJson = strpos($contentType, self::APPLICATION_JSON) !== false;
        if (! $isApplicationJson) {
            return [];
        }
        $content = json_decode($this->getRawBody($server), true);
        $error = json_last_error();
        if ($error !== JSON_ERROR_NONE) {
            throw new InvalidRequestJsonException(json_last_error_msg());
        }

        return $content;
    }

    private function getRawBody(array $server) : string
    {
        return $server['HTTP_RAW_POST_DATA'] ?? rtrim((string) file_get_contents($this->stdIn));
    }
}
