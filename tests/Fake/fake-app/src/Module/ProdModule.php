<?php

declare(strict_types=1);

namespace FakeVendor\HelloWorld\Module;

use BEAR\Package\Context\ProdModule as PackageProdModule;
use Ray\Di\AbstractModule;

class ProdModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        AppModule::$modules[] = $this::class;
        $this->install(new PackageProdModule());
    }
}
