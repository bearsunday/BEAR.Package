<?php

namespace Demo\Helloworld\Module;

use Ray\Di\AbstractModule;
use BEAR\Package;
use BEAR\Package\Module;
use BEAR\Package\Provide as ProvideModule;
use BEAR\Sunday\Module as SundayModule;
use Ray\Di\Module\InjectorModule;

class AppModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        // di - application
        $this->bind()->annotatedWith('app_name')->toInstance('Demo\Helloworld');
        $this->bind('BEAR\Sunday\Extension\Application\AppInterface')->to('Demo\Helloworld\App');
        $this->install(new SundayModule\Framework\FrameworkModule);
        $this->install(new SundayModule\Constant\NamedModule(['tmp_dir' => sys_get_temp_dir()]));
        $this->install(new InjectorModule($this));
    }
}
