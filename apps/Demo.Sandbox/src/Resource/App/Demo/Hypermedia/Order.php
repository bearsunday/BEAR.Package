<?php

namespace Demo\Sandbox\Resource\App\Demo\Hypermedia;

use BEAR\Resource\ResourceObject;
use BEAR\Resource\Link;
use BEAR\Sunday\Inject\TmpDirInject;

/**
 * Order
 */
class Order extends ResourceObject
{
    /**
     * @var array
     */
    public $links = [
        'payment' => ''
    ];

    /**
     * @param string $drink
     *
     * @return Order
     */
    public function onPost($drink)
    {
        // body
        $this['drink'] = $drink;

        // link
        $price = 250;
        $this->links['payment'] = [Link::HREF => 'app://self/demo/hypermedia/payment?price=' . $price];

        // 201 created
        $this->code = 201;

        return $this;
    }
}
