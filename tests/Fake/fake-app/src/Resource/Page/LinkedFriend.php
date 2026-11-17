<?php

declare(strict_types=1);

namespace FakeVendor\HelloWorld\Resource\Page;

use BEAR\Resource\ResourceObject;

/** Target of LinkedUser's "friend" #[Link]; counts calls so a test can assert it was followed exactly once. */
class LinkedFriend extends ResourceObject
{
    public static int $callCount = 0;

    public function onGet(string $id): static
    {
        self::$callCount++;
        $this->body = [
            'id' => $id,
            'resource' => 'friend',
        ];

        return $this;
    }
}
