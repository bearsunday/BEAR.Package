<?php

declare(strict_types=1);

namespace BEAR\Package;

use BEAR\AppMeta\AppMeta;
use BEAR\Package\Provide\Router\CliRouter;
use BEAR\Package\Provide\Router\WebRouter;
use BEAR\Package\Provide\Transfer\CliResponder;
use BEAR\Sunday\Extension\Application\AbstractApp;
use BEAR\Sunday\Extension\Application\AppInterface;
use BEAR\Sunday\Provide\Transfer\HttpResponder;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\VoidCache;
use FakeVendor\HelloWorld\FakeDep;
use FakeVendor\HelloWorld\Module\AppModule;
use FakeVendor\HelloWorld\Resource\Page\Dep;
use PHPUnit\Framework\TestCase;

class BootstrapTest extends TestCase
{
    /**
     * @var AppMeta
     */
    private $appMeta;

    protected function setUp() : void
    {
        $this->appMeta = new AppMeta('FakeVendor\HelloWorld');
        AppModule::$modules = [];
        (new Unlink)->force(__DIR__ . '/Fake/fake-app/var/tmp');
    }

    public function testBuiltInCliModule()
    {
        $app = (new Bootstrap)->getApp('FakeVendor\HelloWorld', 'cli-app');
        $this->assertInstanceOf(CliRouter::class, $app->router);
        $this->assertInstanceOf(CliResponder::class, $app->responder);
        $this->assertInstanceOf(AppInterface::class, $app);
    }

    public function testGetApp()
    {
        $app = (new Bootstrap)->getApp('FakeVendor\HelloWorld', 'prod-app');
        $this->assertInstanceOf(AppInterface::class, $app);
        $this->assertInstanceOf(WebRouter::class, $app->router);
        $this->assertInstanceOf(HttpResponder::class, $app->responder);
    }

    public function testCache()
    {
        $cache = new ArrayCache();
        $app1 = (new Bootstrap)->newApp(new AppMeta('FakeVendor\HelloWorld', 'prod-cli-app'), 'prod-cli-app', $cache);
        $app2 = (new Bootstrap)->newApp(new AppMeta('FakeVendor\HelloWorld', 'prod-cli-app'), 'prod-cli-app', $cache);
        $this->assertSame(serialize($app1), serialize($app2));
    }

    public function testNewApp()
    {
        $appMeta = new AppMeta('FakeVendor\HelloWorld');
        $newTmpDir = $appMeta->tmpDir;
        $appMeta->tmpDir = $newTmpDir;
        $app = (new Bootstrap)->newApp($appMeta, 'app', new VoidCache);
        $this->assertInstanceOf(AppInterface::class, $app);
    }

    public function testInvalidContext()
    {
        $this->expectException(\BEAR\Package\Exception\InvalidContextException::class);

        (new Bootstrap)->getApp('FakeVendor\HelloWorld', 'invalid');
    }

    public function testCompileOnDemandInDevelop()
    {
        (new Bootstrap)->getApp('FakeVendor\HelloWorld', 'app');
        $app = (new Bootstrap)->getApp('FakeVendor\HelloWorld', 'app');
        $this->assertInstanceOf(AppInterface::class, $app);
        /** @var Dep $dep */
        $dep = $app->resource->uri('page://self/dep')();
        $this->assertInstanceOf(FakeDep::class, $dep->depInterface);
        $this->assertInstanceOf(FakeDep::class, $dep->dep);
    }

    public function testSerializeApp()
    {
        $app = (new Bootstrap)->getApp('FakeVendor\HelloWorld', 'prod-app');
        $this->assertInstanceOf(AbstractApp::class, unserialize(serialize($app)));
    }

    public function testCompileOnDemandInDevelopment()
    {
        (new Bootstrap)->getApp('FakeVendor\HelloWorld', 'app');
        $app = (new Bootstrap)->getApp('FakeVendor\HelloWorld', 'app');
        $this->assertInstanceOf(AppInterface::class, $app);
        /** @var Dep $dep */
        $dep = $app->resource->uri('page://self/dep')();
        $this->assertInstanceOf(FakeDep::class, $dep->depInterface);
        $this->assertInstanceOf(FakeDep::class, $dep->dep);
    }

    public function testCompileOnDemandInProduction()
    {
        (new Bootstrap)->getApp('FakeVendor\HelloWorld', 'prod-app');
        $app = (new Bootstrap)->getApp('FakeVendor\HelloWorld', 'prod-app');
        $this->assertInstanceOf(AppInterface::class, $app);
        /** @var Dep $dep */
        $dep = $app->resource->uri('page://self/dep')();
        $this->assertInstanceOf(FakeDep::class, $dep->depInterface);
        $this->assertInstanceOf(FakeDep::class, $dep->dep);
    }
}
