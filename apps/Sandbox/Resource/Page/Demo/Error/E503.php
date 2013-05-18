<?php

namespace Sandbox\Resource\Page\Demo\Error;

use BEAR\Resource\AbstractObject;

/**
 * 503
 */
class E503 extends AbstractObject
{
    public function onGet()
    {
        $this->code = 503;
        return $this;
    }
}
