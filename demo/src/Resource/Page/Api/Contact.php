<?php

declare(strict_types=1);

namespace MyVendor\MyProject\Resource\Page\Api;

use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\ResourceObject;
use BEAR\Sunday\Inject\ResourceInject;

class Contact extends ResourceObject
{
    use ResourceInject;

    /**
     * @Embed(rel="contact", src="/api/user/friend{?id}")
     */
    public function onGet($id)
    {
        return $this;
    }
}
