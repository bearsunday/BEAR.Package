<?php
/**
 * @package    Sandbox
 * @subpackage Resource
 */
namespace Sandbox\Resource\App\First\Greeting;

use BEAR\Resource\AbstractObject;

/**
 * My first AOP
 *
 * @package    Sandbox
 * @subpackage Resource
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
