<?php

namespace Demo\Sandbox\Params;

use BEAR\Resource\ParamProviderInterface;
use BEAR\Resource\ParamInterface;

class FakeTime implements ParamProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(ParamInterface $param)
    {
        $time = '2013-09-08 00:00:00';
        return $param->inject($time);
    }
}
