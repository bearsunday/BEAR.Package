<?php

namespace MyVendor\MyApp\Resource\App;

use BEAR\Resource\Annotation\Link;
use BEAR\Resource\ResourceObject;

class User extends ResourceObject
{
    /**
     * @Link(rel="friend", href="/friend?id={friend_id}")
     */
    public function onGet($id)
    {
        $this['id'] = $id;
        $this['friend_id'] = 'f' . $id;

        return $this;
    }
}
