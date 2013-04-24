<?php

namespace Sandbox\Module;

use BEAR\Package\Provide as PackageModule;
use BEAR\Sunday\Module as SundayModule;

/**
 * Application module for API
 */
class ApiModule extends ProdModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->install(new ProdModule);
        $this->install(new PackageModule\ResourceView\HalModule($this));
        //$this->install(new SundayModule\Resource\JsonModule($this));
    }
}
