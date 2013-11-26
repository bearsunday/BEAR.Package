<?php

namespace Sandbox\Resource\Page;

use BEAR\Resource\ResourceObject;
use BEAR\Sunday\Inject\ResourceInject;
use Ray\Di\Di\Inject;

/**
 * Untitled
 */
class Index2 extends ResourceObject
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
