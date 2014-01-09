<?php
/**
 * This file is part of the BEAR.Packages package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Module\Resource;

use BEAR\Package\Provide as ProvideModule;
use BEAR\Sunday\Module as SundayModule;
use Ray\Di\AbstractModule;
use Ray\Di\Scope;

/**
 * DevResource Module
 *
 * + SQL Log
 * + Resource log
 * + Resource dev rendering
 */
class DevResourceModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        // DBAL debug
        $this->bind('Doctrine\DBAL\Logging\SQLLogger')->to('Doctrine\DBAL\Logging\DebugStack')->in(Scope::SINGLETON);
        // Common debug
        $this->bind('BEAR\Resource\InvokerInterface')->to('BEAR\Resource\DevInvoker')->in(Scope::SINGLETON);
        $this->install(new ProvideModule\ResourceView\DevRendererModule($this));
    }
}
