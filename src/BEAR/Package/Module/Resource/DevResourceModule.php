<?php
/**
 * This file is part of the BEAR.Packages package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Module\Resource;

use Ray\Di\AbstractModule;
use BEAR\Sunday\Module as SundayModule;
use BEAR\Package\Provide as ProvideModule;
use Ray\Di\Scope;

/**
 * DevResource Module
 *
 * + SQL Log
 * + Resource log
 * + Resource dev rendering
 *
 * @package    Sandbox
 * @subpackage Module
 */
class DevResourceModule extends AbstractModule
{
    /**
     * (non-PHPdoc)
     * @see Ray\Di.AbstractModule::configure()
     */
    protected function configure()
    {
        // DBAL debug
        $this->bind('Doctrine\DBAL\Logging\SQLLogger')->to('Doctrine\DBAL\Logging\DebugStack')->in(Scope::SINGLETON);
        // Common debug
        $this->bind('BEAR\Resource\InvokerInterface')->to('BEAR\Resource\DevInvoker')->in(Scope::SINGLETON);
        $this->install(new ProvideModule\ResourceView\DevRendererModule($this));
        $this->installDevLogger();
    }

    /**
     * Provide debug information
     *
     * depends FrameworkModule
     */
    private function installDevLogger()
    {
        $logger = $this->requestInjection(__NAMESPACE__ . '\Logger');
        $this->bindInterceptor(
            $this->matcher->subclassesOf('BEAR\Resource\Object'),
            $this->matcher->annotatedWith('BEAR\Sunday\Annotation\Log'),
            [$logger]
        );
    }

}
