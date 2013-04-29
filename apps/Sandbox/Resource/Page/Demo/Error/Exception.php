<?php

namespace Sandbox\Resource\Page\Demo\Error;

use BEAR\Resource\AbstractObject;

/**
 * Error page
 *
 * throw exception in resource method.
 */
class Exception extends AbstractObject
{
    public function onGet()
    {
        throw new \RuntimeException('exception thrown in ' . __METHOD__);
    }
}
