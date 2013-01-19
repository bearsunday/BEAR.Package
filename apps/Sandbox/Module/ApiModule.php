<?php
/**
 * @package    Sandbox
 * @subpackage Module
 */
namespace Sandbox\Module;

use BEAR\Sunday\Module as SundayModule;

/**
 * Application module for API
 *
 * @package    Sandbox
 * @subpackage Module
 */
class ApiModule extends ProdModule
{
    /**
     * (non-PHPdoc)
     * @see Ray\Di.AbstractModule::configure()
     */
    protected function configure()
    {
        $this->install(new ProdModule);
        $this->install(new SundayModule\Resource\HalModule($this));
        //$this->install(new SundayModule\Resource\JsonModule($this));
    }
}
