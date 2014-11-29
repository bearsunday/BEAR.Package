<?php
namespace BEAR\Package\Module\Di;

use Ray\Di\Definition;

class DiCompilerProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DiCompilerProvider
     */
    protected $diCompilerProvider;

    protected function setUp()
    {
        $this->diCompilerProvider = new DiCompilerProvider('Demo\Sandbox', 'test', '/tmp');
    }

    public function testNew()
    {
        $this->assertInstanceOf('\BEAR\Package\Module\Di\DiCompilerProvider', $this->diCompilerProvider);
    }

    public function testGet()
    {
        $diCompiler = $this->diCompilerProvider->get();
        $this->assertInstanceOf('\Ray\Di\DiCompiler', $diCompiler);
    }

    public function testGetMultipleTimes()
    {
        $this->diCompilerProvider->get();
        $diCompiler = $this->diCompilerProvider->get();
        $this->assertInstanceOf('\Ray\Di\DiCompiler', $diCompiler);
    }
}
