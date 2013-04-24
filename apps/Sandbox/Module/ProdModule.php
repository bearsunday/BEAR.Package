<?php

namespace Sandbox\Module;

use BEAR\Package\Module as PackageModule;
use BEAR\Sunday\Module as SundayModule;
use Ray\Di\AbstractModule;

/**
 * Production module
 */
class ProdModule extends AbstractModule
{
    protected function configure()
    {
        $this->install(new App\AppModule('prod'));
    }
}
