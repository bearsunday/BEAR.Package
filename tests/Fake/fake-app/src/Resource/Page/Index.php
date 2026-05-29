<?php

declare(strict_types=1);

namespace FakeVendor\HelloWorld\Resource\Page;

use BEAR\Resource\ResourceObject;

class Index extends ResourceObject
{
    /** @var array{greeting: string} */
    public $body;
    public function onGet(string $name = 'BEAR.Sunday'): self
    {
        $this->body = [
            'greeting' => 'Hello ' . $name
        ];

        return $this;
    }
}
