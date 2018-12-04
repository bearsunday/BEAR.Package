<?php

declare(strict_types=1);

namespace FakeVendor\HelloWorld\Resource\App;

use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\ResourceObject;

class Emb extends ResourceObject
{
    /**
     * @Embed(rel="user", src="/user{?id}")
     */
    public function onGet($id)
    {
        return $this;
    }
}
