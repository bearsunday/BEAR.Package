<?php

declare(strict_types=1);

namespace FakeVendor\HelloWorld\Resource\App;

use BEAR\Resource\Annotation\Link;
use BEAR\Resource\ResourceObject;

class User extends ResourceObject
{
    /**
     * @Link(rel="friend", href="/friend?id={friend_id}")
     * @Link(rel="org", href="/org?id={org_id}")
     */
    public function onGet($id, $type = 'defaultType')
    {
        unset($type);
        $this->body = [
            'id' => $id,
            'friend_id' => 'f' . $id,
            'org_id' => 'o' . $id
        ];

        return $this;
    }

    /**
     * @Link(rel="friend", href="/friend?id={friend_id}")
     */
    public function onPost($id)
    {
        $this['id'] = $id;
        $this['friend_id'] = 'f' . $id;

        return $this;
    }
}
