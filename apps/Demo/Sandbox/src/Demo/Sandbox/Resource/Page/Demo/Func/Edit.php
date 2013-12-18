<?php

namespace Demo\Sandbox\Resource\Page\Demo\Func;

use BEAR\Resource\ResourceObject as Page;
use BEAR\Resource\RenderInterface;

/**
 * Index page
 */
class Edit extends Page
{
    public function onGet()
    {
        edit($this);
    }
}
