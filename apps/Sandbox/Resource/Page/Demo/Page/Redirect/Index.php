<?php

namespace Sandbox\Resource\Page\Demo\Page\Redirect;

use BEAR\Resource\AbstractObject as Page;

/**
 * Redirect page
 */
class Index extends Page
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
