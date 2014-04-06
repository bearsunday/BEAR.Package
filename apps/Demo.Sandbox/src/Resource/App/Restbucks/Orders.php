<?php

namespace Demo\Sandbox\Resource\App\Restbucks;

use BEAR\Resource\ResourceObject;
use BEAR\Resource\Link;
use BEAR\Sunday\Inject\TmpDirInject;
use DirectoryIterator;

class Orders extends ResourceObject
{
    public static $orders;

    public function onGet()
    {
        // load
        $this->loadOrder();
        return $this;
    }

    private function loadOrder()
    {
        // load
        foreach (self::$orders as $order) {
            $id = $order->body['id'];
            $order->links['edit'] = [Link::HREF => "app://self/restbucks/order?id={$id}"];
            $order->links['self'] = [Link::HREF => "app://self/restbucks/order?id={$id}"];
            $this->body['order'][] = $order;
        }
    }
}
