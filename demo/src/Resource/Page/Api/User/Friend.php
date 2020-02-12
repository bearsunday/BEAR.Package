<?php

declare(strict_types=1);

namespace MyVendor\MyProject\Resource\Page\Api\User;

use BEAR\Resource\ResourceObject;

class Friend extends ResourceObject
{
    public $body = [
        ['id' => '1', 'name' => 'Athos'],
        ['id' => '2', 'name' => 'Porthos'],
        ['id' => '3', 'name' => 'Aramis'],
    ];

    public function onGet()
    {
        return $this;
    }
}
