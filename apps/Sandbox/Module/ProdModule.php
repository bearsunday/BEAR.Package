<?php
/**
 * @package    Sandbox
 * @subpackage Module
 */
namespace Sandbox\Module;

use BEAR\Sunday\Module as SundayModule;
use BEAR\Package\Module as PackageModule;

use Ray\Di\AbstractModule;

/**
 * Production module
 *
 * @package    Sandbox
 * @subpackage Module
 */
class ProdModule extends AbstractModule
{
    /**
     * (non-PHPdoc)
     * @see Ray\Di.AbstractModule::configure()
     */
    protected function configure()
    {
        $config = include __DIR__ . '/config.php';
        // dependency binding (DI)
        $this->install(new Common\AppModule($config));
        // aspect weaving (AOP)
        $this->install(new PackageModule\Package\AspectModule($this));
    }
}
