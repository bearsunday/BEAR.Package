<?php

namespace BEAR\Package\Context;

use BEAR\Sunday\Annotation\DefaultSchemeHost;
use Ray\Di\Injector;

class ApiModuleTest extends \PHPUnit_Framework_TestCase
{
    public function testModule()
    {
        $scheme = (new Injector(new ApiModule))->getInstance('', DefaultSchemeHost::class);
        $this->assertSame('app://self', $scheme);
    }
}
