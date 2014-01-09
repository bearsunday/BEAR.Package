<?php

namespace Demo\Sandbox\Resource\Page\Demo\Error;

use BEAR\Resource\ResourceObject;

/**
 * 503
 */
class E503 extends ResourceObject
{
    public function onGet()
    {
        $this->code = 503;
        return $this;
    }
}
