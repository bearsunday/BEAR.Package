<?php

namespace FakeVendor\HelloWorld\Resource\App;

use BEAR\Resource\ResourceObject;

class Task extends ResourceObject
{
    public function onGet($id = null)
    {
        return (string) $id;
    }
}
