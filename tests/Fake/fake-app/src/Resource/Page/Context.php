<?php

declare(strict_types=1);

namespace FakeVendor\HelloWorld\Resource\Page;

use BEAR\Resource\ResourceObject;
use Ray\Di\Di\Named;

class Context extends ResourceObject
{
    private $a;
    private $b;

    /**
     * @Named("a=usr_db,b=job_db")
     */
    public function __construct($a, $b)
    {
        $this->a = $a;
        $this->b = $b;
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
