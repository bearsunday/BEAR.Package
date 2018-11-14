<?php
namespace MyVendor\MyProject\Module;

use BEAR\Package\AbstractAppModule;
use BEAR\Package\PackageModule;

class AppModule extends AbstractAppModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $appDir = $this->appMeta->appDir;
        require_once $appDir . '/env.php';
        $this->install(new PackageModule);
    }
}
