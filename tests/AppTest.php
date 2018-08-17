<?php
/**
 * This file is part of the BEAR.Package package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Package;

use BEAR\AppMeta\AppMeta;
use BEAR\Package\Provide\Router\CliRouter;
use BEAR\Package\Provide\Router\WebRouter;
use BEAR\Package\Provide\Transfer\CliResponder;
use BEAR\Sunday\Extension\Application\AbstractApp;
use BEAR\Sunday\Extension\Application\AppInterface;
use BEAR\Sunday\Provide\Transfer\HttpResponder;
use FakeVendor\HelloWorld\FakeDep;
use FakeVendor\HelloWorld\Module\AppModule;
use FakeVendor\HelloWorld\Resource\Page\Dep;
use PHPUnit\Framework\TestCase;

class AppTest extends TestCase
{
    /**
     * @var AppMeta
     */
    private $appMeta;

    public function setUp()
    {
        $this->appMeta = new AppMeta('FakeVendor\HelloWorld');
        AppModule::$modules = [];
        (new Unlink)->force(__DIR__ . '/Fake/fake-app/var/tmp');
    }

    public function testBuiltInCliModule()
    {
        $app = (new App)('FakeVendor\HelloWorld', 'cli-app');
        $this->assertInstanceOf(CliRouter::class, $app->router);
        $this->assertInstanceOf(CliResponder::class, $app->responder);
        $this->assertInstanceOf(AppInterface::class, $app);
    }

    public function testGetApp()
    {
        $app = (new App)('FakeVendor\HelloWorld', 'prod-app');
        $this->assertInstanceOf(AppInterface::class, $app);
        $this->assertInstanceOf(WebRouter::class, $app->router);
        $this->assertInstanceOf(HttpResponder::class, $app->responder);
    }

    /**
     * @expectedException \BEAR\Package\Exception\InvalidContextException
     */
    public function testInvalidContext()
    {
        (new App)('FakeVendor\HelloWorld', 'invalid');
    }

    public function testCompileOnDemandInDevelop()
    {
        (new App)('FakeVendor\HelloWorld', 'app');
        $app = (new App)('FakeVendor\HelloWorld', 'app');
        $this->assertInstanceOf(AppInterface::class, $app);
        /** @var Dep $dep */
        $dep = $app->resource->uri('page://self/dep')();
        $this->assertInstanceOf(FakeDep::class, $dep->depInterface);
        $this->assertInstanceOf(FakeDep::class, $dep->dep);
    }

    public function testSerializeApp()
    {
        $app = (new App)('FakeVendor\HelloWorld', 'prod-app');
        $this->assertInstanceOf(AbstractApp::class, unserialize(serialize($app)));
    }

    public function testCompileOnDemandInDevelopment()
    {
        (new App)('FakeVendor\HelloWorld', 'app');
        $app = (new App)('FakeVendor\HelloWorld', 'app');
        $this->assertInstanceOf(AppInterface::class, $app);
        /** @var Dep $dep */
        $dep = $app->resource->uri('page://self/dep')();
        $this->assertInstanceOf(FakeDep::class, $dep->depInterface);
        $this->assertInstanceOf(FakeDep::class, $dep->dep);
    }

    public function testCompileOnDemandInProduction()
    {
        (new App)('FakeVendor\HelloWorld', 'prod-app');
        $app = (new App)('FakeVendor\HelloWorld', 'prod-app');
        $this->assertInstanceOf(AppInterface::class, $app);
        /** @var Dep $dep */
        $dep = $app->resource->uri('page://self/dep')();
        $this->assertInstanceOf(FakeDep::class, $dep->depInterface);
        $this->assertInstanceOf(FakeDep::class, $dep->dep);
    }

    public function testDotEnv()
    {
        $envFile = __DIR__ . '/Fake/fake-app/.env';
        (new App)('FakeVendor\HelloWorld', 'prod-app', $envFile);
        $this->assertSame(1, $_ENV['FAKE_VAL']);
    }
}
