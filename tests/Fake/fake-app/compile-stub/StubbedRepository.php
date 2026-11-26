<?php

declare(strict_types=1);

namespace FakeVendor\HelloWorld;

class StubbedRepository
{
    public function store(): string
    {
        return 'stub';
    }
}
