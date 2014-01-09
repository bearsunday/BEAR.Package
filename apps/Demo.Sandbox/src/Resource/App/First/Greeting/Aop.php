<?php

namespace Demo\Sandbox\Resource\App\First\Greeting;

use BEAR\Resource\ResourceObject;

/**
 * My first AOP
 */
class Aop extends ResourceObject
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
