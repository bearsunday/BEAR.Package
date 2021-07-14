<?php

declare(strict_types=1);

namespace BEAR\Package\Context;

use BEAR\AppMeta\Meta;
use Doctrine\Common\Annotations\PsrCachedReader;
use Doctrine\Common\Annotations\Reader;
use FakeVendor\HelloWorld\Module\MetaModule;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

class ProdModuleTest extends TestCase
{
    public function testModule(): void
    {
        $reader = (new Injector(new ProdModule(new MetaModule(new Meta('FakeVendor\HelloWorld')))))->getInstance(Reader::class);
        $this->assertInstanceOf(PsrCachedReader::class, $reader);
    }
}
