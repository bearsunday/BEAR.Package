<?php

namespace {$namespace};

use BEAR\Resource\AbstractObject;
use BEAR\Sunday\Inject\ResourceInject;
use Ray\Di\Di\Inject;

/**
 * Untitled
 */
class {$class} extends AbstractObject
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
