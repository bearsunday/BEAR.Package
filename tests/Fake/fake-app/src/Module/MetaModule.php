<?php
/**
 * This file is part of the BEAR.Package package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace FakeVendor\HelloWorld\Module;

use BEAR\Package\AbstractAppModule;

class MetaModule extends AbstractAppModule
{
    public static $appDir;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        self::$appDir = $this->appMeta->appDir;
    }
}
