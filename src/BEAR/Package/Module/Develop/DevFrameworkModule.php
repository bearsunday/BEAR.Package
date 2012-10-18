<?php
/**
 * Module
 *
 * @package    Sandbox
 * @subpackage Module
 */
namespace BEAR\Package\Module\Develop;

use Ray\Di\AbstractModule;
use BEAR\Sunday\Module as SundayModule;

/**
 * Package module
 *
 * @package    Sandbox
 * @subpackage Module
 */
class DevFrameworkModule extends AbstractModule
{
    /**
     * (non-PHPdoc)
     * @see Ray\Di.AbstractModule::configure()
     */
    protected function configure()
    {
        $this->bind('BEAR\Resource\InvokerInterface')->to('BEAR\Resource\DevInvoker');
        $this->install(new SundayModule\TemplateEngine\DevRendererModule);
        $this->install(new SundayModule\FrameworkModule($this));
        $this->installDevLogger();
    }

    /**
     * Provide debug information
     *
     * depends FrameworkModule
     */
    private function installDevLogger()
    {
        $logger = $this->requestInjection('BEAR\Sunday\Interceptor\Logger');
        $this->bindInterceptor(
            $this->matcher->subclassesOf('BEAR\Resource\Object'),
            $this->matcher->annotatedWith('BEAR\Sunday\Annotation\Log'),
            [$logger]
        );
    }

}
