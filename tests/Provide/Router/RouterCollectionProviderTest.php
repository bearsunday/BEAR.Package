<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Router;

use BEAR\Package\FakeWebRouter;
use BEAR\Package\PackageModule;
use BEAR\Sunday\Extension\Router\RouterInterface;
use PHPUnit\Framework\TestCase;
use Ray\Di\AbstractModule;
use Ray\Di\Injector;

class RouterCollectionProviderTest extends TestCase
{
    public function testPrimaryRouter(): void
    {
        $module = new class extends AbstractModule
        {
            protected function configure(): void
            {
                $this->install(new PackageModule());
                $this->bind(RouterInterface::class)->annotatedWith('primary_router')->toInstance(new FakeWebRouter('page://self', new HttpMethodParams()));
                $this->bind(WebRouterInterface::class)->to(WebRouter::class);
                $this->bind(RouterInterface::class)->toProvider(RouterCollectionProvider::class);
            }
        };
        $i = new Injector();
        $injector = new Injector($module);
        $router = $injector->getInstance(RouterInterface::class);
        $this->assertInstanceOf(RouterInterface::class, $router);
    }
}
