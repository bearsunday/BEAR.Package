<?php
/**
 * This file is part of the BEAR.Package package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
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
