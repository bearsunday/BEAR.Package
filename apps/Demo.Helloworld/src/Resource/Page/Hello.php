<?php

namespace Demo\Helloworld\Resource\Page;

use BEAR\Resource\ResourceObject;

class Hello extends ResourceObject
{
    /**
     * @param string $name
     */
    public function onGet($name = 'World')
    {
        $this->body = 'Hello ' . $name;
        return $this;
    }
}
