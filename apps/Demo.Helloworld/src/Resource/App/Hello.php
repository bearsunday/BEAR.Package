<?php

namespace Demo\Helloworld\Resource\App;

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
        $this['greeting'] = 'Hello ' . $name;
        $this['time'] = date('r');
        return $this;
    }
}
