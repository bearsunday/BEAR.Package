<?php
/**
 * This file is part of the BEAR.Package package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
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
    public function setStdIn($stdIn)
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

    private function unsafeMethod($method, array $server, array $post) : array
    {
        $params = $this->getParams($method, $server, $post);

        if ($method === 'post') {
            list($method, $params) = $this->getOverrideMethod($method, $server, $params);
        }

        return [$method, $params];
    }

    private function getOverrideMethod($method, array $server, array $params) : array
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
    private function getParams($method, array $server, array $post) : array
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
     *
     * parsed standard input in form-urlencoded or JSON in application/json
     */
    private function phpInput(array $server) : array
    {
        $contentType = $this->getContentType($server);
        if (! $contentType) {
            return [];
        }
        $isFormUrlEncoded = strpos($contentType, self::FORM_URL_ENCODE) !== false;
        if ($isFormUrlEncoded) {
            parse_str(rtrim(file_get_contents($this->stdIn)), $put);

            return $put;
        }
        $isApplicationJson = strpos($contentType, self::APPLICATION_JSON) !== false;
        if ($isApplicationJson) {
            $content = json_decode(file_get_contents($this->stdIn), true);
            $error = json_last_error();
            if ($error !== JSON_ERROR_NONE) {
                throw new InvalidRequestJsonException(json_last_error_msg());
            }

            return $content;
        }

        return [];
    }

    private function getContentType(array $server) : string
    {
        if (isset($server[self::CONTENT_TYPE])) {
            return $server[self::CONTENT_TYPE];
        }
        if (isset($server[self::HTTP_CONTENT_TYPE])) {
            return $server[self::HTTP_CONTENT_TYPE];
        }

        return '';
    }
}
