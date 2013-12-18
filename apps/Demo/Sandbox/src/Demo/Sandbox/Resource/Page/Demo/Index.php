<?php

namespace Demo\Sandbox\Resource\Page\Demo;

use BEAR\Resource\ResourceObject as Page;
use BEAR\Sunday\Annotation\Cache;

class Index extends Page
{
    /**
     * @Cache
     */
    public function onGet()
    {
        return $this;
    }
}
