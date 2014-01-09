<?php

namespace Demo\Sandbox\Resource\Page\Demo\Page\Http;

use BEAR\Resource\ResourceObject as Page;

/**
 * 404
 */
class Index extends Page
{
    public function onGet()
    {
        $this->code = 404;

        return $this;
    }
}
