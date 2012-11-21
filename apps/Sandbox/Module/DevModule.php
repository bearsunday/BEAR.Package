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
        $config = include __DIR__ . '/config.php';
        $this->install(new SundayModule\Constant\NamedModule($config));
        $this->install(new SundayModule\Framework\FrameworkModule($this));
        $this->install(new Common\AppModule($this));
        $this->install(new PackageModule\Resource\DevResourceModule($this));
    }
}
