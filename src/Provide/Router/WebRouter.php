<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Router;

use BEAR\Sunday\Annotation\DefaultSchemeHost;
use BEAR\Sunday\Extension\Router\RouterInterface;
use BEAR\Sunday\Extension\Router\RouterMatch;

use function parse_url;

/**
 * @psalm-import-type Globals from RouterInterface
 * @psalm-import-type Server from RouterInterface
 */
class WebRouter implements RouterInterface, WebRouterInterface
{
    public function __construct(
        #[DefaultSchemeHost]
        private string $schemeHost,
        private HttpMethodParamsInterface $httpMethodParams,
    ) {
    }

    /**
     * @param array{HTTP_X_HTTP_METHOD_OVERRIDE?: string, REQUEST_METHOD: string, REQUEST_URI: string, ...} $server
     * @param array{_GET: array<string|array>, _POST: array<string|array>}                              $globals
     */

    /**
     * {@inheritdoc}
     *
     * @param Globals $globals
     * @param Server  $server
     */
    public function match(array $globals, array $server)
    {
        $requestUri = $server['REQUEST_URI'];
        $get = $globals['_GET'];
        $post = $globals['_POST'];
        [$method, $query] = $this->httpMethodParams->get($server, $get, $post);
        $path = $this->schemeHost . parse_url($requestUri, 5); // 5 = PHP_URL_PATH

        return new RouterMatch($method, $path, $query);
    }

    /**
     * {@inheritdoc}
     */
    public function generate($name, $data)
    {
        return false;
    }
}
