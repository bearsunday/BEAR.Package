<?php

declare(strict_types=1);

namespace BEAR\Package\Context;

use BEAR\AppMeta\Meta;
use BEAR\Package\Provide\Error\ErrorPageFactoryInterface;
use BEAR\Package\Provide\Error\ProdVndErrorPageFactory;
use FakeVendor\HelloWorld\Module\MetaModule;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

class ProdModuleTest extends TestCase
{
    public function testModule(): void
    {
        $errorPageFactory = (new Injector(new ProdModule(new MetaModule(new Meta('FakeVendor\HelloWorld')))))->getInstance(ErrorPageFactoryInterface::class);
        $this->assertInstanceOf(ProdVndErrorPageFactory::class, $errorPageFactory);
    }
}
