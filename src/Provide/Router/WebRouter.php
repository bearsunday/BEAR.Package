<?php
/**
 * This file is part of the BEAR.Sunday package
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
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
     * @param string                    $schemeHost
     * @param HttpMethodParamsInterface $httpMethodParams
     *
     * @DefaultSchemeHost("schemeHost")
     */
    public function __construct($schemeHost, HttpMethodParamsInterface $httpMethodParams)
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
        list($request->method, $request->query) = $this->httpMethodParams->get($server, $globals['_GET'], $globals['_POST']);
        $request->path = $this->schemeHost . parse_url($server['REQUEST_URI'], PHP_URL_PATH);

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
