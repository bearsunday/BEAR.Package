<?php

namespace Sandbox\Resource\Page\Error;

use BEAR\Resource\AbstractObject;

/**
 * Exception page
 */
class Exception extends AbstractObject
{
    public function onGet()
    {
        throw new \RuntimeException('exception thrown in ' . __METHOD__);
    }
}
