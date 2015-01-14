<?php

namespace BEAR\Package;

use Aura\Router\Router;
use BEAR\Package\Provide\Router\AuraRouter;
use BEAR\Package\Provide\Router\RouterCollection;
use BEAR\Sunday\Extension\Router\RouterInterface;
use FakeVendor\HelloWorld\Module\AppModule;
use Ray\Di\Injector;

class AuraRouterModuleTest extends \PHPUnit_Framework_TestCase
{
    public static $routerClass;

    public function testRouter()
    {
        $injector = new Injector(new AuraRouterModule(new AppModule));
        $router = $injector->getInstance(RouterInterface::class);
        $this->assertInstanceOf(RouterCollection::class, $router);

        $auraRouter = $injector->getInstance(RouterInterface::class, 'primary_router');
        $this->assertInstanceOf(AuraRouter::class, $auraRouter);

        $this->assertInstanceOf(Router::class, self::$routerClass);
    }
}
