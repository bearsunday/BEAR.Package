<?php

declare(strict_types=1);

namespace FakeVendor\HelloWorld\Resource\App;

use BEAR\Resource\ResourceObject;

class ParamSource extends ResourceObject
{
    public static bool $requested = false;

    public function onGet(): static
    {
        self::$requested = true;
        $this->body = ['id' => '1'];

        return $this;
    }
}
