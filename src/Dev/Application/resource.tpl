<?php

namespace {$namespace};

use BEAR\Resource\ResourceObject;
use BEAR\Sunday\Inject\ResourceInject;
use Ray\Di\Di\Inject;

/**
 * Untitled
 */
class {$class} extends ResourceObject
{
    use ResourceInject;

    public $body = [
        'name' => ''
    ];

    public function onGet()
    {
        return $this;
    }
}
