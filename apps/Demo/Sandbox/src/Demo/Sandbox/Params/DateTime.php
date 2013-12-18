<?php

namespace Demo\Sandbox\Params;

use BEAR\Resource\ParamProviderInterface;
use BEAR\Resource\Param;
use DateTimeZone;

class DateTime implements ParamProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(Param $param)
    {
        $dateTime = new \DateTime('now', new DateTimeZone("Asia/Tokyo"));
        return $param->inject($dateTime);
    }
}
