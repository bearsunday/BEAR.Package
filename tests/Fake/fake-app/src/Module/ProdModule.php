<?php

namespace FakeVendor\HelloWorld\Module;

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
