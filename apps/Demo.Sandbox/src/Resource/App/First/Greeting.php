<?php
namespace Demo\Sandbox\Resource\App\First;

use BEAR\Resource\ResourceObject;

/**
 * Greeting resource
 */
class Greeting extends ResourceObject
{
    /**
     * @param string $name
     *
     * @return string
     */
    public function onGet($name)
    {
        return "Hello, {$name}";

        // same as above.
        // $this->body = "Hello, {$name}";
        // return $this;
    }
}
