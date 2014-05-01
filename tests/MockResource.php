<?php

namespace BEAR\Package;

class MockResource extends \BEAR\Resource\ResourceObject
{
    public $headers = ['head1' => 1];
    public $body = [
        'greeting' => 'hello'
    ];

    public function onGet($a, $b)
    {
        $this['posts'] = [$a, $b];

        return $this;
    }
}
