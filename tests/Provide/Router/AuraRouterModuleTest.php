<?php

namespace BEAR\Package\Provide\Router;

use Aura\Router\Router;
use BEAR\AppMeta\AppMeta;
use BEAR\Package\AppMetaModule;
use BEAR\Sunday\Extension\Router\RouterInterface;
use FakeVendor\HelloWorld\Module\AppModule;
use Ray\Di\Injector;

class AuraRouterModuleTest extends \PHPUnit_Framework_TestCase
{
    public static $routerClass;

    public function testRouter()
    {
        $module = (new AuraRouterModule(new AppModule));
        $module->install(new AppMetaModule(new AppMeta('FakeVendor\HelloWorld')));
        $injector = new Injector($module);
        $router = $injector->getInstance(RouterInterface::class);
        $this->assertInstanceOf(RouterCollection::class, $router);

        $auraRouter = $injector->getInstance(RouterInterface::class, 'primary_router');
        $this->assertInstanceOf(AuraRouter::class, $auraRouter);

        $this->assertInstanceOf(Router::class, self::$routerClass);
    }
}
