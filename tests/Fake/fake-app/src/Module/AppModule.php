<?php

declare(strict_types=1);

namespace FakeVendor\HelloWorld\Module;

use BEAR\Package\PackageModule;
use FakeVendor\HelloWorld\Auth;
use FakeVendor\HelloWorld\FakeDep;
use FakeVendor\HelloWorld\FakeDepInterface;
use FakeVendor\HelloWorld\FakeFoo;
use FakeVendor\HelloWorld\Module\Provider\AuthProvider;
use FakeVendor\HelloWorld\NullInterceptor;
use FakeVendor\HelloWorld\Resource\Page\Dep;
use FakeVendor\HelloWorld\StubbedRepository;
use FakeVendor\HelloWorld\StubbedTx;
use FakeVendor\HelloWorld\StubbedTxInterceptor;
use Ray\Di\AbstractModule;

class AppModule extends AbstractModule
{
    public static $modules = [];

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        self::$modules[] = $this::class;
        $this->bind(FakeDepInterface::class)->to(FakeDep::class);
        $this->install(new PackageModule());
        $this->install(new ContextModule());
        $this->bindInterceptor(
            $this->matcher->subclassesOf(FakeDep::class),
            $this->matcher->startsWith('foo'),
            [NullInterceptor::class]
        );
        $this->bind(FakeFoo::class);
        $this->bind(FakeDep::class);
        $this->bind(Auth::class)->toProvider(AuthProvider::class);
        $this->bind(StubbedRepository::class);
        $this->bindInterceptor(
            $this->matcher->subclassesOf(StubbedRepository::class),
            $this->matcher->annotatedWith(StubbedTx::class),
            [StubbedTxInterceptor::class],
        );
        // .compile.php sets it; a build-time value the compiled scripts keep.
        $this->bind()->annotatedWith('compile_env')->toInstance((string) ($_SERVER['FAKE_APP_COMPILE_ENV'] ?? ''));
    }
}
