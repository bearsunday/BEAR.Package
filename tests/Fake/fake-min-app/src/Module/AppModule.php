<?php

declare(strict_types=1);

namespace FakeVendor\MinApp\Module;

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
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->install(new PackageModule());
    }
}
