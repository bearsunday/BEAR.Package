<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\Router;

use BEAR\Package\AbstractAppMeta;
use BEAR\Sunday\Extension\Router\RouterInterface;
use Ray\Di\ProviderInterface;
use Aura\Router\RouterFactory;

class AuraRouterProvider implements ProviderInterface
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @param AbstractAppMeta $appMeta
     */
    public function __construct(AbstractAppMeta $appMeta)
    {
        $this->router = $router = (new RouterFactory)->newInstance();
        $routeFile = $appMeta->appDir . '/var/conf/aura.router.php';
        if (file_exists($routeFile)) {
            include $routeFile;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        return new AuraRouter($this->router);
    }
}
