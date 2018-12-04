<?php

declare(strict_types=1);

namespace FakeVendor\HelloWorld\Resource\App;

use BEAR\Resource\ResourceObject;

class Scalar extends ResourceObject
{
    public function onGet()
    {
        return 'ak';
    }
}
