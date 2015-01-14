<?php

namespace MyVendor\MyApp\Resource\App;

use BEAR\Resource\ResourceObject;

class Website extends ResourceObject
{
    public function onGet($id)
    {
        $this['url'] = "http:://example.org/{$id}";
        $this['id'] = $id;

        return $this;
    }
}
