<?php

namespace BEAR\Package;

use BEAR\AppMeta\AppMeta;
use BEAR\Sunday\Extension\Application\AppInterface;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\FilesystemCache;
use FakeVendor\HelloWorld\Module\AppModule;

class BootstrapTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        AppModule::$modules = [];
    }

    public function testNewApp()
    {
        $app = (new Bootstrap)->newApp(new AppMeta('FakeVendor\HelloWorld'), 'prod-app', new ArrayCache);
        $this->assertInstanceOf(AppInterface::class, $app);
        $expect = ['FakeVendor\HelloWorld\Module\AppModule', 'FakeVendor\HelloWorld\Module\ProdModule'];
        $this->assertSame($expect, AppModule::$modules);
    }

    public function testBuiltInCliModule()
    {
        $app = (new Bootstrap)->newApp(new AppMeta('FakeVendor\HelloWorld'), 'cli-app', new ArrayCache);
        $this->assertInstanceOf(AppInterface::class, $app);
    }

    public function testContextCacheModule()
    {
        $app = (new Bootstrap)->newApp(new AppMeta('FakeVendor\HelloWorld'), 'app');
        $this->assertInstanceOf(AppInterface::class, $app);
    }

    public function testCache()
    {
        $app1 = (new Bootstrap)->newApp(new AppMeta('FakeVendor\HelloWorld'), 'cli-app', new FilesystemCache(__DIR__ . '/tmp'));
        $app2 = (new Bootstrap)->newApp(new AppMeta('FakeVendor\HelloWorld'), 'cli-app', new FilesystemCache(__DIR__ . '/tmp'));
        $this->assertSame(serialize($app1), serialize($app2));
    }
}
