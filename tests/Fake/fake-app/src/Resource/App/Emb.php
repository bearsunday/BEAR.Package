<?php
/**
 * This file is part of the BEAR.Package package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
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
