<?php
/**
 * @package    HelloWorld
 * @subpackage Resource
 */
namespace Helloworld\Resource\App;

use BEAR\Resource\AbstractObject as Page;

/**
 * Hello world
 *
 * @package    HelloWorld
 * @subpackage Resource
 */
class Hello extends Page
{
    /**
     * @param string $name
     *
     * @return Hello
     */
    public function onGet($name)
    {
        $this['greeting'] = 'Hello ' . $name;
        $this['time'] = date('r');
        return $this;
    }
}
