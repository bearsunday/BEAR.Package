<?php

namespace Sandbox\Resource\Page\Demo;

use BEAR\Resource\AbstractObject as Page;
use BEAR\Sunday\Annotation\Cache;
/**
 * Index page
 */
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
