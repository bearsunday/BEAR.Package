<?php

declare(strict_types=1);

namespace FakeVendor\HelloWorld\Resource\Page;

use BEAR\Resource\ResourceObject;
use Ray\Di\Di\Named;

class Context extends ResourceObject
{
    public function __construct(
        #[Named('usr_db')] private $a,
        #[Named('job_db')] private $b
    ){
    }

    public function onGet() : ResourceObject
    {
        $this->body = [
            'a' => $this->a,
            'b' => $this->b
        ];

        return $this;
    }
}
