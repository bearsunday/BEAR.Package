<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Package\Provide\Router;

use Aura\Router\Generator;
use Aura\Router\RouteCollection;
use Aura\Router\RouteFactory;
use Aura\Router\Router;
use BEAR\AppMeta\AbstractAppMeta;
use BEAR\Sunday\Annotation\DefaultSchemeHost;
use Ray\Di\ProviderInterface;

class AuraRouterProvider implements ProviderInterface
{
    /**
     * @var Router
     */
    private $router;

    /**
     * @var
     */
    private $schemeHost;

    /**
     * @param AbstractAppMeta $appMeta
     * @param string          $schemeHost
     *
     * @DefaultSchemeHost("schemeHost")
     */
    public function __construct(AbstractAppMeta $appMeta, $schemeHost)
    {
        $this->schemeHost = $schemeHost;
        $router = new Router(new RouteCollection(new RouteFactory), new Generator);
        $routeFile = $appMeta->appDir . '/var/conf/aura.route.php';
        include $routeFile;
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        return new AuraRouter($this->router, $this->schemeHost, new HttpMethodParams);
    }
}
