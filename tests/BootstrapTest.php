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
use BEAR\Sunday\Extension\Application\AppInterface;
use BEAR\Sunday\Provide\Transfer\HttpResponder;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\VoidCache;
use FakeVendor\HelloWorld\Module\AppModule;

class BootstrapTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AppMeta
     */
    private $appMeta;

    public function setUp()
    {
        $this->appMeta = new AppMeta('FakeVendor\HelloWorld');
        AppModule::$modules = [];
        parent::setUp();
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

    /**
     * @expectedException \BEAR\Package\Exception\InvalidContextException
     */
    public function testInvalidContext()
    {
        (new Bootstrap)->getApp('FakeVendor\HelloWorld', 'invalid');
    }
}
