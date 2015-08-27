<?php

namespace BEAR\Package;

use BEAR\AppMeta\AppMeta;
use BEAR\Package\Exception\InvalidContextException;
use BEAR\Package\Provide\Router\CliRouter;
use BEAR\Package\Provide\Router\WebRouter;
use BEAR\Package\Provide\Transfer\CliResponder;
use BEAR\Sunday\Extension\Application\AppInterface;
use BEAR\Sunday\Provide\Transfer\HttpResponder;
use Doctrine\Common\Cache\FilesystemCache;
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
        $unlink = function ($path) use (&$unlink) {
            foreach (glob(rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*') as $file) {
                is_dir($file) ? $unlink($file) : unlink($file);
                @rmdir($file);
            }
        };
        $this->appMeta = new AppMeta('FakeVendor\HelloWorld');
        $unlink($this->appMeta->tmpDir);
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
        $expect = ['FakeVendor\HelloWorld\Module\AppModule', 'FakeVendor\HelloWorld\Module\ProdModule'];
        $this->assertSame($expect, AppModule::$modules);
    }

    public function testCache()
    {
        $app1 = (new Bootstrap)->newApp(new AppMeta('FakeVendor\HelloWorld'), 'prod-cli-app', new FilesystemCache(__DIR__ . '/tmp'));
        $app2 = (new Bootstrap)->newApp(new AppMeta('FakeVendor\HelloWorld'), 'prod-cli-app', new FilesystemCache(__DIR__ . '/tmp'));
        $this->assertSame(serialize($app1), serialize($app2));
    }

    public function testNewApp()
    {
        $appMeta = new AppMeta('FakeVendor\HelloWorld');
        $newTmpDir = $appMeta->tmpDir . 'new';
        if (! file_exists($newTmpDir)) {
            mkdir($newTmpDir);
        }
        $appMeta->tmpDir = $newTmpDir;
        $app = (new Bootstrap)->newApp($appMeta, 'app', new VoidCache);
        $this->assertInstanceOf(AppInterface::class, $app);
    }

    public function testInvalidContext()
    {
        $this->setExpectedException(InvalidContextException::class);
        (new Bootstrap)->getApp('FakeVendor\HelloWorld', 'invalid');
    }
}
