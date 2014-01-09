<?php

namespace Demo\Sandbox\Params;

use BEAR\Resource\ParamProviderInterface;
use BEAR\Resource\ParamInterface;
use DateTimeZone;

class DateTime implements ParamProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(ParamInterface $param)
    {
        $dateTime = new \DateTime('now', new DateTimeZone("Asia/Tokyo"));
        return $param->inject($dateTime);
    }
}
