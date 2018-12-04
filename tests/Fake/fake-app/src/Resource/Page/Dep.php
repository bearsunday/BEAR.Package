<?php

declare(strict_types=1);

namespace FakeVendor\HelloWorld\Resource\Page;

use BEAR\Resource\ResourceObject;
use FakeVendor\HelloWorld\FakeDep;
use FakeVendor\HelloWorld\FakeDepInterface;

class Dep extends ResourceObject
{
    public $depInterface;
    public $dep;

    public function __construct(FakeDepInterface $depInterface, FakeDep $dep)
    {
        $this->depInterface = $depInterface;
        $this->dep = $dep;
    }

    public function onGet() : ResourceObject
    {
        return $this;
    }
}
