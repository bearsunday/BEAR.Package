<?php
/**
 * This file is part of the BEAR.Package package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Package;

use BEAR\Sunday\Extension\Application\AppInterface;
use FakeVendor\HelloWorld\Module\App;
use PHPUnit\Framework\TestCase;

class AppInjectorTest extends TestCase
{
    public function testGetInstance()
    {
        $app = (new AppInjector('FakeVendor\HelloWorld', 'prod-cli-app'))->getInstance(AppInterface::class);
        $this->assertInstanceOf(App::class, $app);
    }

    /**
     * @expectedException \BEAR\Package\Exception\InvalidContextException
     */
    public function testInvalidContext()
    {
        (new AppInjector('FakeVendor\HelloWorld', '__invalid__'))->getInstance(AppInterface::class);
    }

    /**
     * @expectedException \Ray\Compiler\Exception\NotCompiled
     */
    public function testInvalidInterface()
    {
        (new AppInjector('FakeVendor\HelloWorld', 'prod-cli-app'))->getInstance('__Invalid__');
    }
}
