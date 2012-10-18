<?php
/**
 * App resource
 *
 * @package    Sandbox
 * @subpackage Resource
 */
namespace Sandbox\Resource\Page\Test;

use BEAR\Sunday\Resource\AbstractPage as Page;

/**
 * Redirect page
 *
 * @package    Sandbox
 * @subpackage Resource
 */
class Redirect extends Page
{
    /**
     * @return Redirect
     */
    public function onGet()
    {
        $this->code = 302;
        $this->headers = ['Location' => '/'];
        return $this;
    }
}
