<?php
/**
 * @package    Sandbox
 * @subpackage Module
 */
namespace Helloworld\Module;

use BEAR\Package\Module as PackageModule;
use Ray\Di\AbstractModule;
use BEAR\Package;
use BEAR\Package\Module;
use BEAR\Package\Provide as ProvideModule;
use BEAR\Sunday\Module as SundayModule;

/**
 * Production module
 *
 * @package    Sandbox
 * @subpackage Module
 */
class AppModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        // di - application
        $this->bind('BEAR\Sunday\Extension\Application\AppInterface')->to('Helloworld\App');
        $this->install(new SundayModule\Constant\NamedModule((require __DIR__ . '/config.php')));
        $this->install(new SundayModule\SchemeModule(__NAMESPACE__ . '\SchemeCollectionProvider'));
        $this->install(new SundayModule\Framework\FrameworkModule($this));
    }
}
