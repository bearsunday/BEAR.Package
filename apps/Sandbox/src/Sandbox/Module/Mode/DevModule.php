<?php

namespace Sandbox\Module\Mode;

use BEAR\Package\Module as PackageModule;
use BEAR\Package\Provide\TemplateEngine\Smarty\DevSmartyModule;
use BEAR\Sunday\Module as SundayModule;
use Ray\Di\AbstractModule;
use Ray\Di\Injector;

class DevModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        // smarty
        $smarty = $this->requestInjection('Smarty');
        /** @var $smarty \Smarty */
        $smarty->force_compile = true;

        // bind tmp writable checker
        $checker = $this->requestInjection('\Sandbox\Interceptor\Checker');
        $this->bindInterceptor(
            $this->matcher->subclassesOf('Sandbox\Resource\Page\Index'),
            $this->matcher->startWith('on'),
            [$checker]
        );
    }
}
