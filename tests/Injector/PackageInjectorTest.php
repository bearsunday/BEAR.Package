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
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;
use Ray\Di\AbstractModule;
use Ray\Di\InjectorInterface;
use ReflectionMethod;
use ReflectionProperty;
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

    #[Depends('testOriginalBind')]
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

    public function testUnserializableRootObject(): void
    {
        // Clear memory cache to avoid hitting instances cached by other tests
        (new ReflectionProperty(PackageInjector::class, 'instances'))->setValue([]);

        set_error_handler(static function (int $errno, string $errstr): void {
            throw new Exception($errstr, $errno);
        }, E_USER_WARNING);

        try {
            $this->expectExceptionMessage('Failed to cache the injector(FakeVendor_HelloWorldprod-app).');
            $injector = PackageInjector::getInstance(new Meta('FakeVendor\HelloWorld'), 'prod-app', new NullAdapter());
            $this->assertInstanceOf(InjectorInterface::class, $injector);
        } finally {
            restore_error_handler();
        }
    }

    public function testIsProdReturnsFalseWhenCompileIsUnbound(): void
    {
        // Covers the Unbound catch branch of PackageInjector::isProd().
        // A minimal module that never installs DiCompileModule leaves
        // Compile::class unbound, so getInstance('', Compile::class) throws.
        $module = new class extends AbstractModule {
            protected function configure(): void
            {
            }
        };

        $isProd = new ReflectionMethod(PackageInjector::class, 'isProd');
        $this->assertFalse($isProd->invoke(null, $module));
    }
}
