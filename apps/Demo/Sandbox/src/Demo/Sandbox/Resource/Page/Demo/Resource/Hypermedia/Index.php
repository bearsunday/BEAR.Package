<?php

namespace Demo\Sandbox\Resource\Page\Demo\Resource\Hypermedia;

use BEAR\Resource\ResourceObject as Page;
use BEAR\Sunday\Inject\ResourceInject;
use Ray\Di\Di\Inject;
use BEAR\Sunday\Inject\AInject;

/**
 * Hyper media client
 */
class Index extends Page
{
    use ResourceInject;
    use AInject;

    public $body = [
        'paymentUri' => '',
        'payment' => ''
    ];

    public function onGet()
    {
        $order = $this->resource
            ->post
            ->uri('app://self/demo/hypermedia/order')
            ->withQuery(['drink' => 'latte'])
            ->eager
            ->request();

        $paymentUri = $this->a->href("payment", $order);

        $payment = $this->resource
            ->post
            ->uri($paymentUri)
            ->eager
            ->request();

        $this['payment_uri'] = $paymentUri;
        $this['payment'] = $payment;

        return $this;
    }
}
