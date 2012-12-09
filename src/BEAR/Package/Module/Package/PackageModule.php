<?php
/**
 * Module
 *
 * @package    Sandbox
 * @subpackage Module
 */
namespace BEAR\Package\Module\Package;

use BEAR\Package\Module;
use BEAR\Sunday\Module as SundayModule;
use Ray\Di\AbstractModule;

/**
 * Package module
 *
 * @package    Sandbox
 * @subpackage Module
 */
class PackageModule extends AbstractModule
{
    private $scheme;

    public function __construct(AbstractModule $module, $scheme)
    {
        parent::__construct($module);
        $this->scheme = $scheme;
    }
    /**
     * (non-PHPdoc)
     * @see Ray\Di.AbstractModule::configure()
     */
    protected function configure()
    {
        $this->install(new SundayModule\Framework\FrameworkModule($this));
        $packageDir = dirname(dirname(dirname(dirname(dirname(__DIR__)))));
        $this->bind()->annotatedWith('package_dir')->toInstance($packageDir);
        // package module
        $this->install(new Module\TemplateEngine\SmartyModule\SmartyModule);
        $this->install(new Module\Log\ZfLogModule);
        $this->install(new Module\Output\WebResponseModule);
        $this->install(new Module\Web\RouterModule);
        $this->install(new Module\Web\GlobalsModule);
        $this->install(new Module\ExceptionHandle\HandleModule);
        // Sunday module
        $this->install(new SundayModule\SchemeModule($this->scheme));
        $this->install(new SundayModule\Resource\ApcModule);
        $this->install(new SundayModule\Resource\HalModule);
        $this->install(new SundayModule\WebContext\AuraWebModule);
        $this->install(new SundayModule\Cqrs\CacheModule($this));
    }
}
