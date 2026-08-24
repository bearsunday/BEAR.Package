<?php

declare(strict_types=1);

namespace MyVendor\MyProject\Module;

use BEAR\Package\Context\ProdModule as PackageProdModule;
use BEAR\Package\Module\ReadOnlyAppModule;
use Ray\Di\AbstractModule;

class ProdModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->install(new ReadOnlyAppModule('/tmp/bear-demo/tmp', '/tmp/bear-demo/log'));
        $this->install(new PackageProdModule());
    }
}
