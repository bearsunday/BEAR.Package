<?php
/**
 * This file is part of the BEAR.Package package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Package;

use BEAR\Resource\RenderInterface;
use BEAR\Sunday\Extension\Application\AbstractApp;
use FakeVendor\HelloWorld\Module\App;
use FakeVendor\HelloWorld\Resource\Page\Index;
use PHPUnit\Framework\TestCase;
use Ray\Di\AbstractModule;

class AppInjectorTest extends TestCase
{
    public function testGetInstance()
    {
        $app = (new AppInjector('FakeVendor\HelloWorld', 'prod-cli-app'))->getInstance(AbstractApp::class);
        $this->assertInstanceOf(App::class, $app);
    }

    /**
     * @expectedException \BEAR\Package\Exception\InvalidContextException
     */
    public function testInvalidContext()
    {
        (new AppInjector('FakeVendor\HelloWorld', '__invalid__'))->getInstance(AbstractApp::class);
    }

    /**
     * @expectedException \Ray\Compiler\Exception\NotCompiled
     */
    public function testInvalidInterface()
    {
        (new AppInjector('FakeVendor\HelloWorld', 'prod-cli-app'))->getInstance('__Invalid__');
    }

    public function testGetOverrideInstance()
    {
        $mock = $this->createMock(RenderInterface::class);
        $module = new class($mock) extends AbstractModule {
            private $mock;

            public function __construct(RenderInterface $mock)
            {
                $this->mock = $mock;
            }

            protected function configure()
            {
                $this->bind(RenderInterface::class)->toInstance($this->mock);
            }
        };
        $appInjector = (new AppInjector('FakeVendor\HelloWorld', 'hal-app'));
        $renderer = $appInjector->getOverrideInstance($module, RenderInterface::class);
        $this->assertInstanceOf(RenderInterface::class, $renderer);
        $index = $appInjector->getOverrideInstance($module, Index::class);
        $prop = (new \ReflectionProperty($index, 'renderer'));
        $prop->setAccessible(true);
        $renderer = $prop->getValue($index);
        $this->assertInstanceOf(RenderInterface::class, $renderer);
    }
}
