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
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        /** @var $config array */
        $this->install(new App\AppModule('dev'));
        $this->install(new PackageModule\Resource\DevResourceModule($this));
        // aspect weaving (AOP)
        $this->installWritableChecker();
        // configure for development
//        $this->requestInjection('BEAR\Package\Provide\TemplateEngine\Smarty\DevSmartyModule');
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
