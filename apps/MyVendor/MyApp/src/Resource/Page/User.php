<?php

namespace MyVendor\MyApp\Resource\Page;

use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\ResourceObject;

class User extends ResourceObject
{
    /**
     * @Embed(rel="user1", src="app://self/user{?id}")
     */
    public function onGet($id)
    {
        return $this;
    }
}
