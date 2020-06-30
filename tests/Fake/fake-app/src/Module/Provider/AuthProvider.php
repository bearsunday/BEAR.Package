<?php

declare(strict_types=1);

namespace FakeVendor\HelloWorld\Module\Provider;

use Ray\Di\Provider;

class AuthProvider implements Provider
{
    /**
     * Throw exception when it's not logged in. = compile time.
     */
    public function get()
    {
        throw new \RuntimeException;
    }
}
