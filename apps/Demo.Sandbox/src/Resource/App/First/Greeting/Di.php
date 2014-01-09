<?php

namespace Demo\Sandbox\Resource\App\First\Greeting;

use BEAR\Resource\ResourceObject;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

/**
 * Greeting resource
 */
class Di extends ResourceObject
{
    /**
     * @param string $message
     *
     * @Inject
     * @Named("greeting_msg")
     */
    public function __construct($message = 'aaa')
    {
        $this->message = $message;
    }

    /**
     * @param string $name
     *
     * @return string
     *
     */
    public function onGet($name = 'anonymous')
    {
        return "{$this->message}, {$name}";
    }
}
