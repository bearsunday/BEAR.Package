<?php

declare(strict_types=1);

namespace Import\HelloWorld\Resource\Page;

use BEAR\Resource\ResourceObject;

class Index extends ResourceObject
{
    /** @var array{greeting: string} */
    public $body;

    /** @return static */
    public function onGet(string $name = 'World'): ResourceObject
    {
        $this->body = [
            'greeting' => 'Konichiwa ' . $name,
        ];

        return $this;
    }
}
