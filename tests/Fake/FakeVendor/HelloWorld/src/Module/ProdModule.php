<?php

namespace FakeVendor\HelloWorld\Module;

use BEAR\Package\AppMeta;
use BEAR\Package\PackageModule;
use Ray\Di\AbstractModule;

class ProdModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        AppModule::$modules[] = get_class($this);
    }
}
