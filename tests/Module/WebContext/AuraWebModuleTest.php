<?php

namespace BEAR\Package\Module\WebContext;

use Ray\Di\Injector;

class AuraWebModuleTest extends \PHPUnit_Framework_TestCase
{
    private $testClass;

    protected function setUp()
    {
        $this->testClass = (new Injector(new AuraWebModule))->getInstance(__NAMESPACE__ . '\AuraWebModuleTestClass');
    }

    public function testGetWebContextProviderInstance()
    {
        $this->assertInstanceOf(__NAMESPACE__ . '\WebContextProvider', $this->testClass->webContextProvider);
    }
}
