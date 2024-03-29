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
    }
}
