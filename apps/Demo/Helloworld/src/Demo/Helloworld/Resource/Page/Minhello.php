<?php

namespace Demo\Helloworld\Resource\Page;

use BEAR\Resource\ResourceObject;

/**
 * Hello world - min
 */
class Minhello extends ResourceObject
{
    /**
     * @var string
     */
    public $body = 'Hello World !';

    public function onGet()
    {
        return $this;
    }
}
