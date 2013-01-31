<?php
/**
 * @package    Skeleton
 * @subpackage Module
 */
namespace Skeleton\Module;

use BEAR\Sunday\Module as SundayModule;
use BEAR\Package\Module as PackageModule;
use Ray\Di\AbstractModule;
use Ray\Di\Scope;

/**
 * Dev module
 *
 * @package    Skeleton
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
        $config = (require __DIR__ . '/config/dev.php') + (require __DIR__ . '/config/prod.php');
        /** @var $config array */
        $this->install(new App\AppModule($config));
        $this->install(new PackageModule\Resource\DevResourceModule($this));
    }
}
