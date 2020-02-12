<?php

declare(strict_types=1);

namespace MyVendor\MyProject\Resource\Page;

use BEAR\Resource\ResourceObject;

class Index extends ResourceObject
{
    public function onGet($name = 'BEAR.Sunday') : ResourceObject
    {
        $this->body = [
            'greeting' => 'Hello ' . $name
        ];

        return $this;
    }
}
