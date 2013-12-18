<?php

namespace Demo\Sandbox\Resource\Page\Demo\Func;

use BEAR\Resource\ResourceObject as Page;
use BEAR\Resource\RenderInterface;

/**
 * Index page
 */
class E extends Page
{
    public function onGet()
    {
        e();
    }
}
