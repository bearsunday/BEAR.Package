<?php
namespace MyVendor\MyProject\Module;

use BEAR\Package\PackageModule;
use josegonzalez\Dotenv\Loader as Dotenv;
use Ray\Di\AbstractModule;

class AppModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $appDir = dirname(dirname(__DIR__));
        Dotenv::load([
            'filepath' => dirname(dirname(__DIR__)) . '/.env',
            'toEnv' => true
        ]);
        $this->install(new PackageModule);
    }
}
