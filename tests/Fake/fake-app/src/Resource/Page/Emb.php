<?php

declare(strict_types=1);

namespace FakeVendor\HelloWorld\Resource\Page;

use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\ResourceObject;

class Emb extends ResourceObject
{
    /**
     * @Embed(rel="user", src="app://self/user?id=1")
     */
    public function onGet()
    {
        return $this;
    }
}
