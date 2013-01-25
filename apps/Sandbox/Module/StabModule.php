<?php
/**
 * @package    Sandbox
 * @subpackage Module
 */
namespace Sandbox\Module;

use Ray\Di\AbstractModule;
use BEAR\Package\Module\Stab\StabModule as PackageStabModule;
use BEAR\Sunday\Interceptor\Stab;

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
        $stab = include __DIR__ . '/common/stab/resource.php';
        $this->install(new PackageStabModule($stab));
    }
}
