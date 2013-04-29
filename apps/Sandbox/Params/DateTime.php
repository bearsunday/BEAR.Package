<?php

namespace Sandbox\Params;

use BEAR\Resource\ParamProviderInterface;
use BEAR\Resource\Param;

/**
 * Provide current clock
 *
 */
class CurrentTime implements ParamProviderInterface
{
    public function __invoke(Param $param)
    {
        $dateTime = new \DateTime('now', new DateTimeZone("Asia/Tokyo"));
        return $param->inject($dateTime);
    }
}
