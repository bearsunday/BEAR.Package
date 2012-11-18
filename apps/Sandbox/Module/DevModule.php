<?php
/**
 * @package    Sandbox
 * @subpackage Module
 */
namespace Sandbox\Module;

use BEAR\Sunday\Module as SundayModule;
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
        $config = include __DIR__ . '/config.php';
        $this->bind('Doctrine\DBAL\Logging\SQLLogger')->to('Doctrine\DBAL\Logging\DebugStack')->in(Scope::SINGLETON);
        $this->install(new SundayModule\Constant\NamedModule($config));
        $this->install(new SundayModule\Framework\DevToolModule);
        $this->install(new Common\AppModule($this));
    }
}
