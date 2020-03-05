<?php

declare(strict_types=1);

namespace MyVendor\MyProject\Resource\Page;

use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\ResourceObject;

class User extends ResourceObject
{
    /**
     * @Embed(rel="user", src="/api/user{?id}")
     */
    public function onGet($id)
    {
        return $this;
    }
}
