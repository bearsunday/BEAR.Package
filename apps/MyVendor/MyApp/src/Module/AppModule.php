<?php

namespace MyVendor\MyApp\Module;

use BEAR\Package\AppMeta;
use BEAR\Package\PackageModule;
use Ray\Di\AbstractModule;
use BEAR\Package\HalModule;

class AppModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->install(new PackageModule(new AppMeta('MyVendor\MyApp')));
        $this->override(new HalModule);
    }
}
