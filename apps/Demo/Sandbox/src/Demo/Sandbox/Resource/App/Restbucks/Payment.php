<?php

namespace Demo\Sandbox\Resource\App\Restbucks;

use BEAR\Resource\ResourceObject;
use BEAR\Sunday\Inject\TmpDirInject;
use BEAR\Resource\RenderInterface;
use Ray\Di\Di\Named;

class Payment extends ResourceObject
{
    use TmpDirInject;

    /**
     * @param $id
     *
     * @return $this
     */
    public function onGet($id)
    {
        $resourceFile = "{$this->tmpDir}/payment{$id}";
        if (file_exists($resourceFile)) {
            $this->code = 200;

            return $this;
        }
        $this->code = 401;
        return $this;
    }

    /**
     * @param int    $id
     * @param string $card_no
     * @param string $expires
     * @param string $name
     * @param int    $amount
     *
     * @return Payment
     */
    public function onPut($id, $card_no, $expires, $name, $amount)
    {
        // load
        $resourceFile = "{$this->tmpDir}/payment{$id}";
        // update
        $this['card_no'] = $card_no;
        $this['expires'] = $expires;
        $this['name'] = $name;
        $this['amount'] = $amount;
        // 201 created
        $this->code = 201;
        // save
        file_put_contents($resourceFile, json_encode($this->body));
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
