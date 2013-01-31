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
        $config = require __DIR__ . '/config/prod.php';
        /** @var $config array */
        $this->install(new Common\AppModule($config));
    }
}
