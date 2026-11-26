<?php

declare(strict_types=1);

namespace FakeVendor\HelloWorld;

/** compile-stub/StubbedRepository.php replaces it at compile, without the constructor or #[StubbedTx]. */
class StubbedRepository
{
    public function __construct(
        public FakeFoo $foo,
    ) {
    }

    #[StubbedTx]
    public function store(): string
    {
        return 'stored';
    }
}
