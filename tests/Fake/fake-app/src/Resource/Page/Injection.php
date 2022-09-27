<?php

declare(strict_types=1);

namespace FakeVendor\HelloWorld\Resource\Page;

use BEAR\Resource\ResourceObject;
use FakeVendor\HelloWorld\FakeDepInterface;
use FooName\FooInterface;


class Injection extends ResourceObject
{
    public FakeDepInterface $foo;
    public function __construct(FakeDepInterface $foo)
    {
        $this->foo = $foo;
    }
}
