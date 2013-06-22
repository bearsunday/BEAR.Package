<?php
namespace BEAR\Package\Module\Package;

use BEAR\Package;
use BEAR\Package\Module;
use BEAR\Package\Provide as ProvideModule;
use BEAR\Sunday\Module as SundayModule;
use BEAR\Sunday\Module\Cqrs\CacheModule as CqrsModule;
use Ray\Di\AbstractModule;
use Ray\Di\Di\Scope;
use Ray\Di\Module\InjectorModule;

/**
 * Package module
 */
class AopModule extends AbstractModule
{
    protected function configure()
    {
        // Package module
        $this->install(new Package\Module\Database\Dbal\DbalModule($this));
    }
}
