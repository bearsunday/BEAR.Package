<?php

namespace FakeVendor\HelloWorld\Module;

use BEAR\AppMeta\AppMeta;
use BEAR\Package\PackageModule;
use Ray\Di\AbstractModule;

class AppModule extends AbstractModule
{
    public static $modules = [];
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        self::$modules[] = get_class($this);
        $this->install(new PackageModule(new AppMeta('FakeVendor\HelloWorld')));
    }
}
