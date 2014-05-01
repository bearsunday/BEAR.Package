<?php
namespace BEAR\Package\Module\Package;

use BEAR\Package;
use BEAR\Package\Module;
use BEAR\Package\Provide as ProvideModule;
use BEAR\Sunday\Module as SundayModule;
use Ray\Di\AbstractModule;
use Ray\Di\Di\Scope;

class AopModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        // Package module
        $this->install(new Package\Module\Database\Dbal\DbalModule($this));
    }
}
