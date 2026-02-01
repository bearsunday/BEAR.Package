<?php

declare(strict_types=1);

namespace MyVendor\MyProject\Resource\Page\Api;

use BEAR\Resource\ResourceObject;

class Website extends ResourceObject
{
    public function onGet(int $id): static
    {
        $this->body = [
            'url' => "http:://example.org/{$id}"
        ];

        return $this;
    }
}
