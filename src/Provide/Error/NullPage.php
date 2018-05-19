<?php
/**
 * This file is part of the BEAR.Package package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Package\Provide\Error;

use BEAR\Resource\ResourceObject;

class NullPage extends ResourceObject
{
    public function onGet() : ResourceObject
    {
        return $this;
    }
}
