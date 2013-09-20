<?php

namespace Sandbox\Params;

use BEAR\Resource\ParamProviderInterface;
use BEAR\Resource\Param;

class FakeTime implements ParamProviderInterface
{
    public function __invoke(Param $param)
    {
        $time = '2013-09-08 00:00:00';
        return $param->inject($time);
    }
}
