<?php

declare(strict_types=1);

namespace BEAR\Package\Context;

use BEAR\Sunday\Annotation\DefaultSchemeHost;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

class ApiModuleTest extends TestCase
{
    public function testModule()
    {
        $scheme = (new Injector(new ApiModule))->getInstance('', DefaultSchemeHost::class);
        $this->assertSame('app://self', $scheme);
    }
}
