<?php

declare(strict_types=1);

namespace MyVendor\MyProject\Resource\Page\Api;

use BEAR\RepositoryModule\Annotation\Cacheable;
use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\Annotation\Link;
use BEAR\Resource\ResourceObject;

/**
 * @Cacheable
 */
class User extends ResourceObject
{
    /**
     * @Link(rel="profile", href="/api/profile{?id}")
     * @Embed(rel="website", src="/api/website{?id}")
     * @Embed(rel="contact", src="/api/contact{?id}")
     */
    public function onGet($id)
    {
        $this->body += [
            'id' => $id,
            'name' => 'Koriym'
        ];

        return $this;
    }
}
