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
     * {@inheritdoc}
     */
    public function match(array $globals, array $server)
    {
        $request = new RouterMatch;
        [$request->method, $request->query] = $this->httpMethodParams->get($server, $globals['_GET'], $globals['_POST']);
        $request->path = $this->schemeHost . parse_url($server['REQUEST_URI'], 5); // 5 = PHP_URL_PATH

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
