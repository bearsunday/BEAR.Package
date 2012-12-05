<?php
/**
 * @package    Sandbox
 * @subpackage Module
 */
namespace Sandbox\Module;

use BEAR\Sunday\Module as SundayModule;
use BEAR\Package\Module as PackageModule;
use Ray\Di\AbstractModule;
use Ray\Di\Scope;

/**
 * Dev module
 *
 * @package    Sandbox
 * @subpackage Module
 */
class DevModule extends AbstractModule
{
    /**
     * (non-PHPdoc)
     * @see Ray\Di.AbstractModule::configure()
     */
    protected function configure()
    {
        $this->bind('Doctrine\DBAL\Logging\SQLLogger')->to('Doctrine\DBAL\Logging\DebugStack')->in(Scope::SINGLETON);
        $this->install(new ProdModule);
        $this->install(new PackageModule\Resource\DevResourceModule($this));
        $this->installWritableChecker();
    }

    /**
     * Check writable directory
     */
    private function installWritableChecker()
    {
        // bind tmp writable checker
        $checker = $this->requestInjection('\Sandbox\Interceptor\Checker');
        $this->bindInterceptor(
            $this->matcher->subclassesOf('Sandbox\Resource\Page\Index'),
            $this->matcher->startWith('on'),
            [$checker]
        );
    }
}
