<?php

declare(strict_types=1);

namespace FakeVendor\HelloWorld\Resource\Page;

use BEAR\Resource\Annotation\Link;
use BEAR\Resource\ResourceObject;

/** Fixture for the CLI #link= fragment end-to-end test; its "friend" link leads to LinkedFriend. */
class LinkedUser extends ResourceObject
{
    #[Link(rel: 'friend', href: 'page://self/linked-friend?id={friend_id}')]
    public function onGet(string $id): static
    {
        $this->body = [
            'id' => $id,
            'friend_id' => 'f' . $id,
        ];

        return $this;
    }
}
