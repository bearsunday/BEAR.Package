<?php

declare(strict_types=1);

namespace FakeVendor\HelloWorld\Resource\Page;

use BEAR\Resource\Annotation\ResourceParam;
use BEAR\Resource\ResourceObject;

class ParamConsumer extends ResourceObject
{
    public function onGet(#[ResourceParam(uri: 'app://self/param-source', param: 'id')] string $id): static
    {
        $this->body = ['id' => $id];

        return $this;
    }
}
