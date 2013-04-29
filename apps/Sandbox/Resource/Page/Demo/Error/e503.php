<?php
namespace Sandbox\Resource\Page\Demo\Error;

use BEAR\Resource\AbstractObject;
use BEAR\Sunday\Inject\ResourceInject;

/**
 * Error page
 *
 * return 503 header
 */
class e503 extends AbstractObject
{
    public function onGet()
    {
        $this->code = 503;
        return $this;
    }
}
