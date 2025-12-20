<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Router;

use BEAR\Sunday\Annotation\DefaultSchemeHost;
use BEAR\Sunday\Extension\Router\RouterInterface;
use BEAR\Sunday\Extension\Router\RouterMatch;
use Override;

use function assert;
use function parse_url;

use const PHP_URL_PATH;

/**
 * @psalm-import-type Globals from RouterInterface
 * @psalm-import-type Server from RouterInterface
 * @psalm-suppress ClassMustBeFinal
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
     * {@inheritDoc}
     *
     * @param Globals $globals Superglobals containing user input ($_GET, $_POST)
     * @param Server  $server  Server variables containing request data
     *
     * @psalm-taint-source input $globals
     * @psalm-taint-source input $server
     */
    #[Override]
    public function match(array $globals, array $server)
    {
        $requestUri = $server['REQUEST_URI'];
        $get = $globals['_GET'];
        $post = $globals['_POST'];
        [$method, $query] = $this->httpMethodParams->get($server, $get, $post);
        $parsedPath = parse_url($requestUri, PHP_URL_PATH);
        assert($parsedPath !== null && $parsedPath !== false);
        $path = $this->schemeHost . $parsedPath;

        return new RouterMatch($method, $path, $query);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function generate($name, $data)
    {
        return false;
    }
}
