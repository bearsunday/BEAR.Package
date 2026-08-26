<?php

declare(strict_types=1);

namespace FakeVendor\HelloWorld\Module;

use BEAR\Package\Module\ReadOnlyAppModule;
use Ray\Di\AbstractModule;

use function getenv;

/**
 * The install an application's own ProdModule would hold, directories taken from the
 * environment so a test can name them per run - unset, they fall to the machine's temp.
 */
class ReadonlyModule extends AbstractModule
{
    protected function configure()
    {
        $this->install(new ReadOnlyAppModule(
            getenv('FAKE_READONLY_TMP') ?: null,
            getenv('FAKE_READONLY_LOG') ?: null
        ));
    }
}
