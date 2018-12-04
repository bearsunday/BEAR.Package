<?php

declare(strict_types=1);

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
