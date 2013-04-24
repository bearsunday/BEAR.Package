<?php

namespace Sandbox\Module;

use BEAR\Package\Module\Stub\StubModule as PackageStubModule;
use Ray\Di\AbstractModule;

/**
 * Stub module
 */
class StubModule extends AbstractModule
{
    protected function configure()
    {
        $this->install(new App\AppModule('stub'));
        /** @var $stub array */
        $this->install(new PackageStubModule($stub));
    }
}
