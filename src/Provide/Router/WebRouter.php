<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Router;

use BEAR\Sunday\Annotation\DefaultSchemeHost;
use BEAR\Sunday\Extension\Router\RouterInterface;
use BEAR\Sunday\Extension\Router\RouterMatch;

class WebRouter implements RouterInterface, WebRouterInterface
{
    /**
     * @var string
     */
    private $schemeHost;

    /**
     * @var HttpMethodParamsInterface
     */
    private $httpMethodParams;

    /**
     * @DefaultSchemeHost("schemeHost")
     */
    public function __construct(string $schemeHost, HttpMethodParamsInterface $httpMethodParams)
    {
        $this->schemeHost = $schemeHost;
        $this->httpMethodParams = $httpMethodParams;
    }

    /**
     * @param array{HTTP_X_HTTP_METHOD_OVERRIDE?: string, REQUEST_METHOD: string, REQUEST_URI: string } $server
     * @param array{_GET: array<string|array>, _POST: array<string|array>}                              $globals
     */

    /**
     * {@inheritdoc}
     */
    public function match(array $globals, array $server)
    {
        assert(isset($server['REQUEST_URI']));
        assert(isset($server['REQUEST_METHOD']));
        /** @var string $requestUri */
        $requestUri = $server['REQUEST_URI'];
        $request = new RouterMatch;
        /** @var array{HTTP_X_HTTP_METHOD_OVERRIDE?: string, REQUEST_METHOD: string} $server */
        [$request->method, $request->query] = $this->httpMethodParams->get($server, $globals['_GET'], $globals['_POST']);
        $request->path = $this->schemeHost . parse_url($requestUri, 5); // 5 = PHP_URL_PATH

        return $request;
    }

    /**
     * {@inheritdoc}
     */
    public function generate($name, $data)
    {
        return false;
    }
}
