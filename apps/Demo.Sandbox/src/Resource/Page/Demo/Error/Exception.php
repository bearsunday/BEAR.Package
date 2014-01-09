<?php

namespace Demo\Sandbox\Resource\Page\Demo\Error;

use BEAR\Resource\ResourceObject;

/**
 * Error page
 *
 * throw exception in resource method.
 */
class Exception extends ResourceObject
{
    public function onGet()
    {
        throw new \RuntimeException('exception thrown in ' . __METHOD__);
    }
}
