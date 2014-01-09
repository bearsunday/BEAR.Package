<?php

namespace Demo\Sandbox\Resource\Page\Demo\Page\Redirect;

use BEAR\Resource\ResourceObject as Page;

/**
 * Redirect page
 */
class Index extends Page
{
    public function onGet()
    {
        $this->code = 302;
        $this->headers = ['Location' => '/'];

        return $this;
    }
}
