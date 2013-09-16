<?php

namespace Helloworld\Resource\Page;

use BEAR\Resource\ResourceObject;

/**
 * Hello world
 */
class Hello extends ResourceObject
{
    /**
     * @param string $name
     */
    public function onGet($name)
    {
        $this->body = 'Hello ' . $name;
        return $this;
    }
}
