<?php

class Test extends \BEAR\Resource\AbstractObject
{
    public function __construct()
    {}

    public function onGet($a, $b)
    {
        $this['posts'] = [$a, $b];

        return $this;
    }
}
