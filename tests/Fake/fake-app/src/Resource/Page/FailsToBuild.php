<?php

declare(strict_types=1);

namespace FakeVendor\HelloWorld\Resource\Page;

use BEAR\Resource\ResourceObject;
use RuntimeException;

class FailsToBuild extends ResourceObject
{
    public function __construct()
    {
        throw new RuntimeException('store unreachable while built');
    }
}
