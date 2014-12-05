<?php

namespace BEAR\Package\Bootstrap;

use Doctrine\Common\Cache\ArrayCache;
use BEAR\Sunday\Extension\Application\AppInterface;
use FakeVendor\HelloWorld\Module\AppModule;

class BootstrapTest extends \PHPUnit_Framework_TestCase
{
    public function testNewApp()
    {
        $app = (new Bootstrap)->newApp('FakeVendor\HelloWorld', 'prod-app', new ArrayCache);
        $this->assertInstanceOf(AppInterface::class, $app);
        $expect = ['FakeVendor\HelloWorld\Module\AppModule', 'FakeVendor\HelloWorld\Module\ProdModule'];
        $this->assertSame($expect, AppModule::$modules);
    }
}
