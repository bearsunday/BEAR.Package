<?php

namespace Helloworld\Resource\Page;

use BEAR\Resource\AbstractObject as Page;

/**
 * Hello world - min
 */
class Minhello extends Page
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
