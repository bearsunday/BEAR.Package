<?php
/**
 * @package    Sandbox
 * @subpackage Module
 */
namespace Sandbox\Module;

use Ray\Di\AbstractModule;
use BEAR\Package\Module\Stab\StabModule as PackageStabModule;

/**
 * Stab module
 *
 * @package    Sandbox
 * @subpackage Module
 */
class StabModule extends AbstractModule
{
    /**
     * (non-PHPdoc)
     * @see Ray\Di.AbstractModule::configure()
     */
    protected function configure()
    {
        $this->install(new DevModule);
        $stab = include __DIR__ . '/config/stab/resource.php';
        /** @var $stab array */
        $this->install(new PackageStabModule($stab));
    }
}
