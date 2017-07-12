<?php
/**
 * This file is part of the BEAR.Package package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
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
