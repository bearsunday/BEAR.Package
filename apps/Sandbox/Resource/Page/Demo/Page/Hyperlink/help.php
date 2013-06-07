<?php

namespace Sandbox\Resource\Page\Demo\Page\Hyperlink;

use BEAR\Resource\AbstractObject as Page;
use BEAR\Resource\Link;

/**
 * help
 *
 */
class Help extends Page
{
    public $links = [
        'back'   => [Link::HREF => 'page://self/demo/page/hyperlink/']
    ];

    public function onGet()
    {
        return $this;
    }
}
