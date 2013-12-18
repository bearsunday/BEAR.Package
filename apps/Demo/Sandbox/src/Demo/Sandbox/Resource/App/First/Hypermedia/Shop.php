<?php

namespace Demo\Sandbox\Resource\App\First\Hypermedia;

use BEAR\Resource\ResourceObject;
use BEAR\Sunday\Inject\AInject;
use BEAR\Sunday\Inject\ResourceInject;

/**
 * Shop resource
 */
class Shop extends ResourceObject
{
    use ResourceInject;
    use AInject;

    /**
     * @param string $item
     * @param string $card_no
     *
     * @return Shop
     */
    public function onPost($item, $card_no)
    {
        $order = $this
            ->resource
            ->post
            ->uri('app://self/first/hypermedia/order')
            ->withQuery(['item' => $item])
            ->eager
            ->request();

        $payment = $this->a->href('payment', $order);

        $this->resource
            ->put
            ->uri($payment)
            ->withQuery(['card_no' => $card_no])
            ->request();

        $this->code = 204;

        return $this;
    }
}
