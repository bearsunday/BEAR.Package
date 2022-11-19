<?php

declare(strict_types=1);

namespace FakeVendor\HelloWorld\Resource\Page;

use BEAR\Resource\ResourceObject;
use FakeVendor\HelloWorld\FakeDepInterface;
use FooName\FooInterface;


class Injection extends ResourceObject
{
    public function __construct(
        public FakeDepInterface $foo
    )
    {
    }
}
