<?php
/**
 * @package    Sandbox
 * @subpackage Resource
 */
namespace Sandbox\Resource\App\First;

use BEAR\Resource\AbstractObject;

/**
 * Greeting resource
 *
 * @package    Sandbox
 * @subpackage Resource
 */
class Greeting extends AbstractObject
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
        $this->body = "Hello, {$name}";
        return $this;
    }
}
