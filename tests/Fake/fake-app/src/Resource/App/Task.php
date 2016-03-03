<?php

namespace FakeVendor\HelloWorld\Resource\App;

use BEAR\Resource\ResourceObject;

class Task extends ResourceObject
{
    public function onGet($id = null)
    {
        $this->headers['Location'] = '/self?id=10';

        return $this;
    }
}
