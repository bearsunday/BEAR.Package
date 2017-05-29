<?php
namespace BEAR\Package;

use BEAR\AppMeta\AppMeta;
use BEAR\Package\Provide\Logger\PsrLoggerModule;
use Psr\Log\LoggerInterface;
use Ray\Di\Injector;

class PsrLoggerModuleTest extends \PHPUnit_Framework_TestCase
{
    public function testPsrLoggerModule()
    {
        $logger = (new Injector(new PsrLoggerModule(new AppMetaModule(new AppMeta('FakeVendor\HelloWorld')))))->getInstance(LoggerInterface::class);
        $this->assertInstanceOf(LoggerInterface::class, $logger);
    }
}
