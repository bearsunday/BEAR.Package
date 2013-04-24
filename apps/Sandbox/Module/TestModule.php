<?php

namespace Sandbox\Module;

use BEAR\Package\Module as PackageModule;
use BEAR\Sunday\Module;
use BEAR\Sunday\Module\Constant;
use Sandbox\Module\ProdModule;

/**
 * Production module
 */
class TestModule extends ProdModule
{
    protected function configure()
    {
        $this->install(new App\AppModule('test'));
        $this->install(new PackageModule\Resource\NullCacheModule($this));
    }
}
