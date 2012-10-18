<?php
/**
 * App resource
 *
 * @package    Sandbox
 * @subpackage Resource
 */
namespace Sandbox\Resource\App;

use BEAR\Resource\AbstractObject;

/**
 * Performance
 *
 * @package    Sandbox
 * @subpackage Resource
 */
class Performance extends AbstractObject
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
