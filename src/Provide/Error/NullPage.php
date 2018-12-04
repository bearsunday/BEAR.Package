<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Error;

use BEAR\Resource\ResourceObject;

class NullPage extends ResourceObject
{
    public function onGet() : ResourceObject
    {
        return $this;
    }
}
