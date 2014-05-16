<?php

namespace Vendor\MockApp\Resource\App;

use BEAR\Resource\ResourceObject;

class Sparrow extends ResourceObject
{
    public function onGet($id)
    {
        $this['sparrow_id'] = $id;

        return $this;
    }

}
