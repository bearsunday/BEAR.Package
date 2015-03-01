<?php

namespace MyVendor\MyApp\Resource\App;

use BEAR\Package\Annotation\Etag;
use BEAR\RepositoryModule\Annotation\QueryRepository;
use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\Annotation\Link;
use BEAR\Resource\ResourceObject;

/**
 * @QueryRepository
 * @Etag
 */
class User extends ResourceObject
{
    /**
     * @Link(rel="profile", href="/profile{?id}")
     * @Embed(rel="website", src="/website{?id}")
     * @Embed(rel="contact", src="/contact{?id}")
     */
    public function onGet($id)
    {
        $this['id'] = $id;
        $this['name'] = 'Akihito Koriyama';

        return $this;
    }
}
