<?php

declare(strict_types=1);

namespace FakeVendor\HelloWorld\Resource\App;

use BEAR\Resource\ResourceObject;
use FakeVendor\HelloWorld\Auth;

class Login extends ResourceObject
{
    public function __construct(Auth $auth)
    {
    }
}
