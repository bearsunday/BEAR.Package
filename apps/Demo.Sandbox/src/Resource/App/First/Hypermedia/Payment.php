<?php

namespace Demo\Sandbox\Resource\App\First\Hypermedia;

use BEAR\Resource\ResourceObject;

/**
 * Payment resource
 */
class Payment extends ResourceObject
{
    /**
     * @param string $card_no
     *
     * @return Payment
     */
    public function onPut($card_no)
    {
        $this['card_no'] = $card_no;
        return $this;
    }
}
