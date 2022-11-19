<?php

declare(strict_types=1);

namespace BEAR\Package\Injector;

use BEAR\Package\Injector;
use BEAR\Resource\ResourceInterface;
use FakeVendor\HelloWorld\FakeDep;
use FakeVendor\HelloWorld\FakeDep2;
use FakeVendor\HelloWorld\FakeDepInterface;
use FakeVendor\HelloWorld\Resource\Page\Injection;
use PHPUnit\Framework\TestCase;
use Ray\Di\AbstractModule;

use function assert;
use function dirname;

class PackageInjectorTest extends TestCase
{
    public function testOriginalBind(): void
    {
        $injector = Injector::getInstance('FakeVendor\HelloWorld', 'app', dirname(__DIR__) . '/Fake/fake-app');
        $resource = $injector->getInstance(ResourceInterface::class);
        assert($resource instanceof ResourceInterface);
        $page = $resource->newInstance('page://self/injection');
        assert($page instanceof Injection);
        $this->assertInstanceOf(FakeDep::class, $page->foo);
    }

    /** @depends testOriginalBind */
    public function testGetOverrideInstance(): void
    {
        $injector = Injector::getOverrideInstance('FakeVendor\HelloWorld', 'app', dirname(__DIR__) . '/Fake/fake-app', new class extends AbstractModule{
            protected function configure(): void
            {
                $this->bind(FakeDepInterface::class)->to(FakeDep2::class);
            }
        });
        $resource = $injector->getInstance(ResourceInterface::class);
        assert($resource instanceof ResourceInterface);
        $page = $resource->newInstance('page://self/injection');
        assert($page instanceof Injection);
        $this->assertInstanceOf(FakeDep2::class, $page->foo);
    }
}
