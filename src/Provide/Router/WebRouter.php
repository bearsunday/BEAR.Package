<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Router;

use BEAR\Package\Exception\InvalidRequestUriException;
use BEAR\Sunday\Annotation\DefaultSchemeHost;
use BEAR\Sunday\Extension\Router\RouterInterface;
use BEAR\Sunday\Extension\Router\RouterMatch;
use Override;

use function is_string;
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
     * @param Globals $globals
     * @param Server  $server
     *
     * @throws InvalidRequestUriException
     */
    #[Override]
    public function match(array $globals, array $server)
    {
        $requestUri = $server['REQUEST_URI'];
        $get = $globals['_GET'];
        $post = $globals['_POST'];
        [$method, $query] = $this->httpMethodParams->get($server, $get, $post);
        $parsedPath = parse_url($requestUri, PHP_URL_PATH);
        // Not assert(): REQUEST_URI is client input, and "//" is a request line PHP accepts.
        if (! is_string($parsedPath)) {
            throw new InvalidRequestUriException($requestUri);
        }

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
