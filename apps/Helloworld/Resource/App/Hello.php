<?php

namespace Helloworld\Resource\App;

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
        $this['greeting'] = 'Hello ' . $name;
        $this['time'] = date('r');
        return $this;
    }
}
