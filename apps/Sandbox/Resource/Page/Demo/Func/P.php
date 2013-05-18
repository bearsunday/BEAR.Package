<?php

namespace Sandbox\Resource\Page\Demo\Func;

use BEAR\Resource\AbstractObject as Page;
use BEAR\Resource\RenderInterface;

/**
 * Index page
 */
class P extends Page
{
    public function onGet()
    {
        p($_SERVER);
        p($this);
    }
}
