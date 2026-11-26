<?php

declare(strict_types=1);

namespace FakeVendor\HelloWorld\Resource\Page;

use BEAR\Resource\ResourceObject;
use RuntimeException;

class Fails extends ResourceObject
{
    public function onGet(): static
    {
        throw new RuntimeException("store unreachable\nsecond line");
    }
}
