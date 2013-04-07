<?php
/**
 * @package    Sandbox
 * @subpackage Module
 */
namespace Sandbox\Module;

use BEAR\Package\Module as PackageModule;
use BEAR\Sunday\Module as SundayModule;
use Ray\Di\AbstractModule;

/**
 * Dev module
 *
 * @package    Sandbox
 * @subpackage Module
 */
class DevModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $config = (require __DIR__ . '/config/dev.php') + (require __DIR__ . '/config/prod.php');

        /** @var $config array */
        $this->install(new Common\AppModule($config));
        $this->install(new PackageModule\Resource\DevResourceModule($this));
        // aspect weaving (AOP)
        $this->installWritableChecker();
    }

    /**
     * Check writable directory
     */
    private function installWritableChecker()
    {
        // bind tmp writable checker
        $checker = $this->requestInjection('\Sandbox\Interceptor\Checker');
        $this->bindInterceptor(
            $this->matcher->subclassesOf('Sandbox\Resource\Page\Index'),
            $this->matcher->startWith('on'),
            [$checker]
        );
    }
}
