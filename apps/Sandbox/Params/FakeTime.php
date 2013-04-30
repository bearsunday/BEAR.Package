<?php

namespace Sandbox\Params;

use BEAR\Resource\ParamProviderInterface;
use BEAR\Resource\Param;

/**
 * Provide current time
 */
class FakeTime implements ParamProviderInterface
{
    public function __invoke(Param $param)
    {
        $time = '2013-04-29 00:00:00';
        return $param->inject($time);
    }
}
