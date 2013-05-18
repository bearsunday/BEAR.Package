<?php

namespace Sandbox\Resource\Page\Demo\Func;

use BEAR\Resource\AbstractObject as Page;
use BEAR\Resource\RenderInterface;

/**
 * Index page
 */
class Printo extends Page
{
    public function onGet()
    {
        print_o($this);
    }
}
