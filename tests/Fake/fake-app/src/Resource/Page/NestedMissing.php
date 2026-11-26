<?php

declare(strict_types=1);

namespace FakeVendor\HelloWorld\Resource\Page;

use BEAR\Resource\ResourceInterface;
use BEAR\Resource\ResourceObject;

class NestedMissing extends ResourceObject
{
    public function __construct(
        private ResourceInterface $resource,
    ) {
    }

    public function onGet(): static
    {
        $this->resource->get('app://self/no-such-resource');

        return $this;
    }
}
