<?php

declare(strict_types=1);

namespace BEAR\Package\Context;

use BEAR\AppMeta\AppMeta;
use BEAR\Package\AppMetaModule;
use Doctrine\Common\Annotations\PsrCachedReader;
use Doctrine\Common\Annotations\Reader;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

class ProdModuleTest extends TestCase
{
    public function testModule(): void
    {
        $reader = (new Injector(new ProdModule(new AppMetaModule(new AppMeta('FakeVendor\HelloWorld')))))->getInstance(Reader::class);
        $this->assertInstanceOf(PsrCachedReader::class, $reader);
    }
}
