<?php
/**
 * @package    Sandbox
 * @subpackage Resource
 */
namespace Sandbox\Resource\Page\Hello;

use BEAR\Sunday\Resource\AbstractPage as Page;

/**
 * Hello World page
 *
 * @package    Sandbox
 * @subpackage Resource
 */
class World extends Page
{
    /**
     * @return World
     */
    public function onGet()
    {
        $this['greeting'] = 'Hello, World !';
        return $this;
    }
}
