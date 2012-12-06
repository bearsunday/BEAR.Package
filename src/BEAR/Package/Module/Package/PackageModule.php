<?php
/**
 * Module
 *
 * @package    Sandbox
 * @subpackage Module
 */
namespace BEAR\Package\Module\Package;

use BEAR\Package\Module;
use Ray\Di\AbstractModule;

/**
 * Package module
 *
 * @package    Sandbox
 * @subpackage Module
 */
class PackageModule extends AbstractModule
{
    /**
     * (non-PHPdoc)
     * @see Ray\Di.AbstractModule::configure()
     */
    protected function configure()
    {
        $packageDir = dirname(dirname(dirname(dirname(dirname(__DIR__)))));
        $this->bind()->annotatedWith('package_dir')->toInstance($packageDir);
        $this->install(new Module\TemplateEngine\SmartyModule\SmartyModule);
        $this->install(new Module\Database\DoctrineDbalModule($this));
        $this->install(new Module\Log\ZfLogModule);
        $this->install(new Module\Output\WebResponseModule);
        $this->install(new Module\Web\RouterModule);
        $this->install(new Module\Web\GlobalsModule);
        $this->install(new Module\ExceptionHandle\HandleModule);
    }
}
