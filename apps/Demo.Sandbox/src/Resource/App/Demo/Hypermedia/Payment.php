<?php

namespace Demo\Sandbox\Resource\App\Demo\Hypermedia;

use BEAR\Resource\ResourceObject;
use BEAR\Resource\Link;
use BEAR\Sunday\Inject\TmpDirInject;

/**
 * Payment
 */
class Payment extends ResourceObject
{
    /**
     * @param int $price
     */
    public function onPost($price)
    {
        // body
        $this['price'] = $price;

        // 201 created
        $this->code = 201;

        return $this;
    }
}
