<?php

namespace Sandbox\Resource\Page\Demo;

use BEAR\Resource\AbstractObject as Page;

/**
 * Index page
 */
class Index extends Page
{
    public function onGet()
    {
        return $this;
    }
}
