<?php
/**
 * This file is part of the BEAR.Sunday package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\Router;

final class HttpMethodParams implements HttpMethodParamsInterface
{
    const CONTENT_TYPE = 'Content-Type';

    const FORM_URL_ENCODE = 'application/x-www-form-urlencoded';

    const APPLICATION_JSON = 'application/json';

    /**
     * {@inheritdoc}
     */
    public function get(array $server, array $get, array $post) {

        // set the original value
        $method = strtolower($server['REQUEST_METHOD']);

        // early return on GET
        if ($method === 'get') {
            return ['get', $get];
        }
        // must be a POST to do an override
        $override = $this->getOverRideMethod($server, $post);
        if ($override) {
            return [$override, $this->getParams($method, $get, $post, $server)];
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
     * @param array &$post
     *
     * @return bool is override ?
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
        if ($method === 'put' || $method === 'patch'  || $method === 'delete') {

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
        if (! isset($server['Content-Type'])) {
            return [];
        }
        $isFormUrlEncoded = strpos($server[self::CONTENT_TYPE], self::FORM_URL_ENCODE) !== false;
        if($isFormUrlEncoded) {
            parse_str(file_get_contents('php://input'), $put);

            return $put;
        }
        $isApplicationJson = strpos($server[self::CONTENT_TYPE], self::APPLICATION_JSON) !== false;
        if ($isApplicationJson) {
            return json_decode(file_get_contents('php://input'));
        }

        return [];
    }
}
