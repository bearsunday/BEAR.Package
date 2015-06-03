<?php
/**
 * This file is part of the BEAR.Sunday package
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Package\Provide\Router;

use BEAR\Package\Annotation\StdIn;
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
        // must be a POST to do an override
        $override = $this->getOverRideMethod($server, $post);
        if ($override) {
            // must be a POST to do an override
            return [$override, $post];
        }
        if ($method === 'post') {
            return ['post', $post];
        }
        // put / patch /delete
        return [$method, $this->getParams($method, $post, $server)];
    }

    /**
     * HTTP Method override
     *
     * @param array $server
     * @param array $post
     *
     * @return bool|string
     */
    private function getOverRideMethod(array $server, array &$post)
    {
        // look for override in post data
        if (isset($post['_method'])) {
            $method =  strtolower($post['_method']);
            unset($post['_method']);

            return $method;
        }

        // look for override in headers
        if (isset($server['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
            return strtolower($server['HTTP_X_HTTP_METHOD_OVERRIDE']);
        }

        return false;
    }

    /**
     * Return request parameters
     *
     * @param string $method
     * @param array  $post
     * @param array  $server
     *
     * @return array
     */
    private function getParams($method, array $post, array $server)
    {
        if ($method === 'put' || $method === 'patch' || $method === 'delete') {
            return $this->phpInput($server);
        }

        return $post;
    }

    /**
     * Take 'php://input' as input in form-urlencoded or json
     *
     * @param array $server
     *
     * @return array
     */
    private function phpInput(array $server)
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
            $content =  (array) json_decode(file_get_contents($this->stdIn));

            return $content;
        }

        return [];
    }

    /**
     * Return content-type
     *
     * @param array $server
     *
     * @return string '' if no "content" header
     */
    private function getContentType(array $server)
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
