<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Router;

use BEAR\Package\Annotation\StdIn;
use BEAR\Package\Exception\InvalidRequestJsonException;
use Ray\Di\Di\Inject;

use function file_get_contents;
use function in_array;
use function json_decode;
use function json_last_error;
use function json_last_error_msg;
use function parse_str;
use function rtrim;
use function str_contains;
use function strtolower;

use const JSON_ERROR_NONE;

final class HttpMethodParams implements HttpMethodParamsInterface
{
    public const CONTENT_TYPE = 'CONTENT_TYPE';
    public const HTTP_CONTENT_TYPE = 'HTTP_CONTENT_TYPE';
    public const FORM_URL_ENCODE = 'application/x-www-form-urlencoded';
    public const APPLICATION_JSON = 'application/json';
    private string $stdIn = 'php://input';

    #[Inject(optional: true)]
    public function setStdIn(
        #[StdIn]
        string $stdIn,
    ): void {
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

    /**
     * @param array{HTTP_X_HTTP_METHOD_OVERRIDE?: string, ...} $server
     * @param array<string, mixed>                        $post
     *
     * @return array{0: string, 1: array<string, mixed>}
     */
    // phpcs:ignore Squiz.Commenting.FunctionComment.MissingParamName
    private function unsafeMethod(string $method, array $server, array $post): array
    {
        /** @var array{_method?: string} $params */
        $params = $this->getParams($method, $server, $post);

        if ($method === 'post') {
            [$method, $params] = $this->getOverrideMethod($method, $server, $params);
        }

        return [$method, $params];
    }

    /**
     * @param array{HTTP_X_HTTP_METHOD_OVERRIDE?: string, ...} $server
     * @param array{_method?: string}                     $params
     *
     * @return array{0: string, 1: array<string, mixed>}
     */
    // phpcs:ignore Squiz.Commenting.FunctionComment.MissingParamName
    private function getOverrideMethod(string $method, array $server, array $params): array
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
     *
     * @param array{CONTENT_TYPE?: string, HTTP_CONTENT_TYPE?: string, ...} $server
     * @param array<string, mixed>                                     $post
     *
     * @return array<string, mixed>
     */
    // phpcs:ignore Squiz.Commenting.FunctionComment.MissingParamName
    private function getParams(string $method, array $server, array $post): array
    {
        // post data exists
        if ($method === 'post' && ! empty($post)) {
            return $post;
        }

        if (in_array($method, ['post', 'put', 'patch', 'delete'], true)) {
            return $this->phpInput($server);
        }

        return $post;
    }

    /**
     * Return request query by media-type
     *
     * @param array{CONTENT_TYPE?: string, HTTP_CONTENT_TYPE?: string, ...} $server $_SERVER
     *
     * @return array<string, mixed>
     */
    // phpcs:ignore Squiz.Commenting.FunctionComment.MissingParamName
    private function phpInput(array $server): array
    {
        $contentType = $server[self::CONTENT_TYPE] ?? $server[self::HTTP_CONTENT_TYPE] ?? '';
        $isFormUrlEncoded = str_contains($contentType, self::FORM_URL_ENCODE);
        if ($isFormUrlEncoded) {
            parse_str(rtrim($this->getRawBody($server)), $put);

            /** @var array<string, mixed> $put */
            return $put;
        }

        $isApplicationJson = str_contains($contentType, self::APPLICATION_JSON);
        if (! $isApplicationJson) {
            return [];
        }

        /** @var array<string, mixed> $content */
        $content = json_decode($this->getRawBody($server), true);
        $error = json_last_error();
        if ($error !== JSON_ERROR_NONE) {
            throw new InvalidRequestJsonException(json_last_error_msg());
        }

        return $content;
    }

    /** @param array{HTTP_RAW_POST_DATA?: string, ...} $server */
    // phpcs:ignore Squiz.Commenting.FunctionComment.MissingParamName
    private function getRawBody(array $server): string
    {
        return $server['HTTP_RAW_POST_DATA'] ?? rtrim((string) file_get_contents($this->stdIn));
    }
}
