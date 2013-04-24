<?php

namespace Sandbox\Resource\App\First\Greeting;

use BEAR\Resource\AbstractObject;

/**
 * My first AOP
 */
class Aop extends AbstractObject
{
    /**
     * @param string $name
     *
     * @return string
     */
    public function onGet($name = 'anonymous')
    {
        return "Hello, {$name}";
    }
}
