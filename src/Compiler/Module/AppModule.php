<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler\Module;

use BEAR\Package\PackageModule;
use Ray\Di\AbstractModule;

class AppModule extends AbstractModule
{
    protected function configure()
    {
        $this->install(new PackageModule());
    }
}
