<?php
/**
 * @package    Sandbox
 * @subpackage Module
 */
namespace Helloworld\Module;

use BEAR\Sunday\Module as SundayModule;
use BEAR\Package\Module as PackageModule;
use Ray\Di\AbstractModule;

/**
 * Production module
 *
 * @package    Sandbox
 * @subpackage Module
 */
class AppModule extends AbstractModule
{
    /**
     * (non-PHPdoc)
     * @see Ray\Di.AbstractModule::configure()
     */
    protected function configure()
    {
        // di - application
        $this->bind('BEAR\Sunday\Application\Context')->to('Helloworld\App');
        $config = include __DIR__ . '/config.php';
        $this->install(new SundayModule\Constant\NamedModule($config));
        $this->install(new SundayModule\Framework\FrameworkModule($this));
        $this->install(new SundayModule\TemplateEngine\ProdRendererModule);
        $this->install(new SundayModule\Resource\ApcModule);
        $this->install(new SundayModule\SchemeModule(__NAMESPACE__ . '\SchemeCollectionProvider'));
        $this->install(new PackageModule\Output\WebResponseModule);
        $this->install(new PackageModule\Log\ZfLogModule);
        $this->install(new PackageModule\TemplateEngine\SmartyModule\SmartyModule);
//

    }
}
