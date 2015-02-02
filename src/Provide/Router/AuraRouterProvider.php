<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\Router;

use Aura\Router\Generator;
use Aura\Router\RouteCollection;
use Aura\Router\RouteFactory;
use Aura\Router\Router;
use BEAR\Package\AbstractAppMeta;
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
     * @DefaultSchemeHost("schemeHost")
     *
     * @param string $schemeHost
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
