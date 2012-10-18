<?php
/**
 * @package    Sandbox
 * @subpackage Module
 */
namespace Sandbox\Module;

use BEAR\Sunday\Module as SundayModule;
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
        $config = include __DIR__ . '/config.php';
        $this->install(new SundayModule\Constant\NamedModule($config));
        $this->install(new SundayModule\Code\CachedAnnotationModule);
        $this->install(new SundayModule\Framework\FrameworkModule($this));
        $this->install(new SundayModule\TemplateEngine\ProdRendererModule);
        $this->install(new Common\AppModule($this));
    }
}
