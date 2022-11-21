<?php

declare(strict_types=1);

namespace FakeVendor\HelloWorld\Resource\Page;

use BEAR\Resource\ResourceObject;
use FakeVendor\HelloWorld\FakeDep;
use FakeVendor\HelloWorld\FakeDepInterface;

class Dep extends ResourceObject
{
    public function __construct(
        public FakeDepInterface $depInterface,
        public FakeDep $dep
    )
    {
    }

    public function onGet() : ResourceObject
    {
        return $this;
    }
}
