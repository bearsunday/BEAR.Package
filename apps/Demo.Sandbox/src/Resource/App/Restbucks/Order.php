<?php

namespace Demo\Sandbox\Resource\App\Restbucks;

use BEAR\Resource\Link;
use BEAR\Resource\ResourceObject;
use BEAR\Sunday\Inject\TmpDirInject;
use BEAR\Resource\RenderInterface;
use Ray\Di\Di\Named;
use BEAR\Sunday\Inject\ResourceInject;
use \Demo\Sandbox\Resource\App\Restbucks\Orders;

class Order extends ResourceObject
{
    use ResourceInject;
    /**
     * Menu
     *
     * @var array
     */
    private $itemList = [
        'latte' => ['cost' => 2.5],
        'tea' => ['cost' => 2.0]
    ];

    /**
     * @param int $id order id
     */
    public function onGet($id)
    {
        // load
        $this->body = Orders::$orders[$id]->body;

        return $this;
    }

    /**
     * @param string $drink
     *
     * @return Order
     */
    public function onPost($drink)
    {
        if (!isset($this->itemList[$drink])) {
            // 404 not found
            $this->code = 404;

            return $this;
        }
        $this['drink'] = $drink;
        $this['cost'] = $this->itemList[$drink]['cost'];

        // link
        $id = date('is'); // min+sec
        $this['id'] = $id;
        $this->links['payment'] = [Link::HREF => 'app://self/restbucks/payment?id=' . $id];
        // 201 created
        $this->code = 201;
        // save
        Orders::$orders[$id] = $this;

        return $this;
    }

    /**
     * @param int    $id
     * @param string $addition
     * @param string $status
     *
     * @return Order
     */
    public function onPut($id, $addition = null, $status = null)
    {
        // load
        $this->body = Orders::$orders[$id]->body;
        // update
        if ($addition) {
            $this['addition'] = $addition;
        }
        if ($status) {
            $this['status'] = $status;
        }
        // link
        $this->links['payment'] = [Link::HREF => 'app://self/restbucks/payment?id=' . $id];
        // 100 continue
        $this->code = 100;
        // make HAL
        return $this;
    }

    /**
     * Delete
     *
     * @param int $id order id
     *
     * @return Order
     */
    public function onDelete($id)
    {
        unset(Orders::$orders[$id]);
        $this->code = 200;

        return $this;
    }

    /**
     * Set HalRenderer
     *
     * @param RenderInterface $renderer
     *
     * @Ray\Di\Di\Inject
     * @Named("hal")
     */
    public function setRenderer(RenderInterface $renderer)
    {
        $this->renderer = $renderer;
    }

}
