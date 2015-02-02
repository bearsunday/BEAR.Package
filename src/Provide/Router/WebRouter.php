<?php
/**
 * This file is part of the BEAR.Sunday package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\Router;

use BEAR\Sunday\Annotation\DefaultSchemeHost;
use BEAR\Sunday\Extension\Router\RouterInterface;
use BEAR\Sunday\Extension\Router\RouterMatch;
use BEAR\Sunday\Extension\Router\SchemeHost;
use Ray\Di\Di\Inject;

class WebRouter implements RouterInterface, WebRouterInterface
{
    /**
     * @var string
     */
    private $schemeHost;

    /**
     * @DefaultSchemeHost
     *
     * @param string $schemeHost
     */
    public function __construct($schemeHost)
    {
        $this->schemeHost = $schemeHost;
    }

    /**
     * {@inheritdoc}
     */
    public function match(array $globals, array $server)
    {
        $request = new RouterMatch;
        list($request->method, $request->query) = (new HttpMethodParams)->get($server, $globals['_GET'], $globals['_POST']);
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
