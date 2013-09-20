<?php

namespace Sandbox\Params;

use BEAR\Resource\ParamProviderInterface;
use BEAR\Resource\Param;

class CurrentTime implements ParamProviderInterface
{
    public function __invoke(Param $param)
    {
        $time = date("Y-m-d H:i:s", time());
        return $param->inject($time);
    }
}
