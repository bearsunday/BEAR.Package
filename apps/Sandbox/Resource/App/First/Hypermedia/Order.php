<?php

namespace Sandbox\Resource\App\First\Hypermedia;

use BEAR\Resource\AbstractObject;
use BEAR\Resource\Link;

/**
 * Greeting resource
 *
 */
class Order extends AbstractObject
{
    /**
     * @var array
     */
    public $links = [
        'payment' => [Link::HREF => 'app://self/first/hypermedia/payment{?id}', Link::TEMPLATED => true]
    ];

    /**
     * @param string $item
     *
     * @return Order
     */
    public function onPost($item)
    {
        $this['item'] = $item;
        $this['id'] = date('is'); // min+sec
        return $this;
    }
}
