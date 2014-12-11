<?php

namespace BEAR\Package\Context;

use Doctrine\Common\Annotations\CachedReader;
use Ray\Di\Injector;
use Doctrine\Common\Annotations\Reader;

class ProdModuleTest extends \PHPUnit_Framework_TestCase
{
    public function testModule()
    {
        $reader = (new Injector(new ProdModule))->getInstance(Reader::class);
        $this->assertInstanceOf(CachedReader::class, $reader);
    }
}
