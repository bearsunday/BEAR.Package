<?php
/**
 * @package    Skeleton
 * @subpackage Module
 */
namespace Skeleton\Module;

use BEAR\Sunday\Module as SundayModule;
use BEAR\Package\Module as PackageModule;

use Ray\Di\AbstractModule;

/**
 * Production module
 *
 * @package    Skeleton
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
        $config = require dirname(__DIR__) . '/config/prod.php';
        /** @var $config array */
        $this->install(new App\AppModule($config));
    }
}
