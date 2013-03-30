<?php
/**
 * @package Sandbox
 */
namespace Sandbox\Resource\Page\Error;

use BEAR\Resource\AbstractObject;
use BEAR\Sunday\Inject\ResourceInject;
use Pagerfanta\Exception\LogicException;
use Ray\Di\Di\Inject;

/**
 * Error
 *
 * @package Sandbox
 */
class e503 extends AbstractObject
{
    use ResourceInject;

    public function onGet()
    {
        $this->code = 503;
        return $this;
    }
}
