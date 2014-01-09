<?php

namespace Demo\Sandbox\Resource\Page\Demo\Form;

use BEAR\Resource\ResourceObject;
use BEAR\Sunday\Inject\ResourceInject;
use Ray\Di\Di\Inject;
use BEAR\Sunday\Annotation\Form;

/**
 * Contact form using Aura.Input
 */
class Auraform extends ResourceObject
{
    use ResourceInject;

    public $body = [
        'name' => ''
    ];

    /**
     * @Form
     */
    public function onGet()
    {
        $this['code'] = 200;
        return $this;
    }

    /**
     * @Form
     */
    public function onPost(
        $name,
        $email,
        $url,
        $message
    ) {
        $this->code = $this['code'] = 201;
        $this['name'] = $name;
        $this['email'] = $email;
        $this['url'] = $url;
        $this['message'] = $message;

        return $this;
    }
}
