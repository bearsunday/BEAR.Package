<?php

namespace Sandbox\Module;

use BEAR\Package\Module as PackageModule;
use BEAR\Package\Provide\TemplateEngine\Smarty\DevSmartyModule;
use BEAR\Sunday\Module as SundayModule;
use Ray\Di\AbstractModule;
use Ray\Di\Injector;

/**
 * Dev module
 */
class DevModule extends AbstractModule
{
    protected function configure()
    {
        $this->install(new PackageModule\Resource\DevResourceModule($this));

        // aspect weaving (AOP)
        $this->installWritableChecker();
        $this->installDevLogger();

        // configure for development
        $this->modifySmarty();
    }

    private function modifySmarty()
    {
        $smarty = $this->requestInjection('Smarty');
        /** @var $smarty \Smarty */
        $smarty->force_compile = true;
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


    /**
     * Provide debug information
     *
     * depends FrameworkModule
     */
    private function installDevLogger()
    {
        $logger = $this->requestInjection('BEAR\Package\Module\Resource\Logger');
        $this->bindInterceptor(
            $this->matcher->subclassesOf('BEAR\Resource\Object'),
            $this->matcher->annotatedWith('BEAR\Sunday\Annotation\Log'),
            [$logger]
        );
    }
}
