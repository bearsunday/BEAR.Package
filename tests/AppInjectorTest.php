<?php
/**
 * This file is part of the BEAR.Package package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Package;

use BEAR\Resource\JsonRenderer;
use BEAR\Resource\RenderInterface;
use BEAR\Sunday\Extension\Application\AppInterface;
use FakeVendor\HelloWorld\Module\App;
use FakeVendor\HelloWorld\Resource\Page\Index;
use PHPUnit\Framework\TestCase;
use Ray\Di\AbstractModule;

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

    public function testGetOverrideInstance()
    {
        $module = new class extends AbstractModule {
            protected function configure()
            {
                $this->bind(RenderInterface::class)->to(JsonRenderer::class);
            }
        };
        $appInjector = (new AppInjector('FakeVendor\HelloWorld', 'hal-app'));
        $renderer = $appInjector->getOverrideInstance($module, RenderInterface::class);
        $this->assertInstanceOf(JsonRenderer::class, $renderer);
        $index = $appInjector->getOverrideInstance($module, Index::class);
        $prop = (new \ReflectionProperty($index, 'renderer'));
        $prop->setAccessible(true);
        $renderer = $prop->getValue($index);
        $this->assertInstanceOf(JsonRenderer::class, $renderer);
    }
}
