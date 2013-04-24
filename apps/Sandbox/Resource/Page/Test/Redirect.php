<?php

namespace Sandbox\Resource\Page\Test;

use BEAR\Resource\AbstractObject as Page;

/**
 * Redirect page
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
