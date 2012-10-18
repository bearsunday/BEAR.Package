<?php
/**
 * @package    Sandbox
 * @subpackage Resource
 */
namespace Sandbox\Resource\App\First\Greeting;

use BEAR\Resource\AbstractObject;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

/**
 * Greeting resource
 *
 * @package    Sandbox
 * @subpackage Resource
 */
class Di extends AbstractObject
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
