<?php

namespace Sandbox\Resource\Page\Demo\Page\Http;

use BEAR\Resource\AbstractObject as Page;

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
