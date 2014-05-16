<?php

namespace Vendor\MockApp\Resource\App;

use BEAR\Resource\ResourceObject;

class Canary extends ResourceObject
{
    public $body = [
        'name' => 'chill kun'
    ];

    public function onGet()
    {
        return $this;
    }
}
