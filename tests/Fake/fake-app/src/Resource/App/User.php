<?php

namespace FakeVendor\HelloWorld\Resource\App;

use BEAR\Resource\Annotation\Link;
use BEAR\Resource\ResourceObject;
use BEAR\Sunday\Annotation\Cache;

class User extends ResourceObject
{
    /**
     * @Link(rel="friend", href="app://self/friend?id={friend_id}")
     * @Link(rel="org", href="app://self/org?id={org_id}")
     * @Cache(30)
     */
    public function onGet($id)
    {
        $this['id'] = $id;
        $this['friend_id'] = 'f' . $id;
        $this['org_id'] = 'o' . $id;

        return $this;
    }

    /**
     * @Link(rel="friend", href="app://self/friend?id={friend_id}")
     * @Cache(30)
     */
    public function onPost($id)
    {
        $this['id'] = $id;
        $this['friend_id'] = 'f' . $id;

        return $this;

    }
}
