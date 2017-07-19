<?php
/**
 * This file is part of the BEAR.Package package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace FakeVendor\HelloWorld\Module;

use BEAR\Package\Context\ProdModule as PackageProdModule;
use Ray\Di\AbstractModule;

class ProdModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        AppModule::$modules[] = get_class($this);
        $this->install(new PackageProdModule());
    }
}
