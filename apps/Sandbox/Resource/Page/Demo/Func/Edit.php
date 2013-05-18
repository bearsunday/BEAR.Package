<?php

namespace Sandbox\Resource\Page\Demo\Func;

use BEAR\Resource\AbstractObject as Page;
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
