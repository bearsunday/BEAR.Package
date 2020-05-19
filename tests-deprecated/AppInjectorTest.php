<?php

declare(strict_types=1);

namespace BEAR\Package;

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

    public function testInvalidContext()
    {
        $this->expectException(\BEAR\Package\Exception\InvalidContextException::class);

        (new AppInjector('FakeVendor\HelloWorld', '__invalid__'))->getInstance(AppInterface::class);
    }

    public function testInvalidInterface()
    {
        $this->expectException(\Ray\Compiler\Exception\NotCompiled::class);

        (new AppInjector('FakeVendor\HelloWorld', 'prod-cli-app'))->getInstance('__Invalid__');
    }

    public function testGetOverrideInstance()
    {
        /** @var RenderInterface $mock */
        $mock = $this->createMock(RenderInterface::class);
        $module = new class($mock) extends AbstractModule {
            /**
             * @var RenderInterface
             */
            private $mock;

            public function __construct(RenderInterface $mock)
            {
                $this->mock = $mock;
                parent::__construct();
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
