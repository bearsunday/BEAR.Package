<?php

namespace Sandbox\Resource\App\First\Hypermedia;

use BEAR\Resource\AbstractObject;

/**
 * Greeting resource
 */
class Payment extends AbstractObject
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
