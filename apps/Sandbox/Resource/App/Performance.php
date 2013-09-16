<?php

namespace Sandbox\Resource\App;

use BEAR\Resource\ResourceObject;

/**
 * Performance
 */
class Performance extends ResourceObject
{
    /**
     * @return string
     */
    public function onGet()
    {
        $performance = number_format((1 / (microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'])), 2);
        return $performance;
    }
}
