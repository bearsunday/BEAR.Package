<?php

declare(strict_types=1);

namespace FakeVendor\HelloWorld\Module\Provider;

use FakeVendor\HelloWorld\Auth;
use Ray\Di\Provider;

/**
 * A stub to make compile successful.
 */
class AuthProvider implements Provider
{
    public function get()
    {
        return new Auth;
    }
}
