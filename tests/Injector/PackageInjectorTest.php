<?php

declare(strict_types=1);

namespace BEAR\Package\Injector;

use BEAR\AppMeta\Meta;
use BEAR\Package\Injector;
use BEAR\Resource\ResourceInterface;
use Exception;
use FakeVendor\HelloWorld\FakeDep;
use FakeVendor\HelloWorld\FakeDep2;
use FakeVendor\HelloWorld\FakeDepInterface;
use FakeVendor\HelloWorld\Resource\Page\Injection;
use PHPUnit\Framework\TestCase;
use Ray\Di\AbstractModule;
use Ray\Di\InjectorInterface;
use Symfony\Component\Cache\Adapter\NullAdapter;

use function assert;
use function dirname;
use function restore_error_handler;
use function set_error_handler;

use const E_USER_WARNING;

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

    public function testWithNullCacheWarning(): void
    {
        set_error_handler(static function (int $errno, string $errstr): void {
            throw new Exception($errstr, $errno);
        }, E_USER_WARNING);
        $this->expectExceptionMessage('Failed to verify the injector cache.');
        $injector = PackageInjector::getInstance(new Meta('FakeVendor\HelloWorld'), 'app', new NullAdapter());
        $this->assertInstanceOf(InjectorInterface::class, $injector);
        restore_error_handler();
    }
}
