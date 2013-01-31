<?php
/**
 * @package    Skeleton
 * @subpackage Module
 */
namespace Skeleton\Module;

use BEAR\Sunday\Module as SundayModule;
use BEAR\Package\Provide as PackageModule;

/**
 * Application module for API
 *
 * @package    Skeleton
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

        // format module
        $this->install(new PackageModule\ResourceView\HalModule($this));
        //$this->install(new SundayModule\Resource\JsonModule($this));
    }
}
