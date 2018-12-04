<?php

declare(strict_types=1);

namespace FakeVendor\HelloWorld\Resource\App;

use BEAR\Resource\ResourceObject;

class Task extends ResourceObject
{
    public function onPost($id = null)
    {
        unset($id);
        $this->headers['Location'] = '/?id=10';
        $this['dummy_not_for_rendering'] = '1';

        return $this;
    }
}
