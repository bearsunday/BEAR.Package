<?php

namespace MyVendor\MyApp\Resource\App;

use BEAR\Resource\ResourceObject;
use BEAR\Sunday\Inject\ResourceInject;

class Contact extends ResourceObject
{
    use ResourceInject;

    public function onGet($id)
    {
        $this['contact'] = $this->resource->get->uri('app://self/user/friend')->withQuery(['id' => $id])->eager->request();

        return $this;
    }
}
