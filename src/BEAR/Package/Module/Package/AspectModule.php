<?php
/**
 * Module
 *
 * @package    Sandbox
 * @subpackage Module
 */
namespace BEAR\Package\Module\Package;

use BEAR\Sunday\Module as SundayModule;
use BEAR\Package\Module;
use Ray\Di\AbstractModule;

/**
 * Package module
 *
 * @package    Sandbox
 * @subpackage Module
 */
class AspectModule extends AbstractModule
{
    /**
     * (non-PHPdoc)
     * @see Ray\Di.AbstractModule::configure()
     */
    protected function configure()
    {
        $this->install(new Module\Database\DoctrineDbalModule($this));
    }
}
