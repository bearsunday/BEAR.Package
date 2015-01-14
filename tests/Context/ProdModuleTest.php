<?php

namespace BEAR\Package\Context;

use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Annotations\Reader;
use Ray\Di\Injector;

class ProdModuleTest extends \PHPUnit_Framework_TestCase
{
    public function testModule()
    {
        $reader = (new Injector(new ProdModule))->getInstance(Reader::class);
        $this->assertInstanceOf(CachedReader::class, $reader);
    }
}
