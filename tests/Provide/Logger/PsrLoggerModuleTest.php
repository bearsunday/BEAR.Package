<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Logger;

use BEAR\AppMeta\AppMeta;
use BEAR\Package\AppMetaModule;
use BEAR\Package\Context\ProdModule;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Ray\Di\Injector;

class PsrLoggerModuleTest extends TestCase
{
    public function testPsrLoggerModule()
    {
        $logger = (new Injector(new PsrLoggerModule(new AppMetaModule(new AppMeta('FakeVendor\HelloWorld')))))->getInstance(LoggerInterface::class);
        $this->assertInstanceOf(LoggerInterface::class, $logger);
    }

    public function testProdPsrLoggerModule()
    {
        $logger = (new Injector(new ProdModule(new PsrLoggerModule(new AppMetaModule(new AppMeta('FakeVendor\HelloWorld'))))))->getInstance(LoggerInterface::class);
        $this->assertInstanceOf(LoggerInterface::class, $logger);
    }
}
