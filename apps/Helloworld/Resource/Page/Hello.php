<?php

namespace Helloworld\Resource\Page;

use BEAR\Resource\AbstractObject as Page;

/**
 * Hello world
 */
class Hello extends Page
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
