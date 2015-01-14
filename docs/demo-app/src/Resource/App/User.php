<?php

namespace MyVendor\MyApp\Resource\App;

use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\Annotation\Link;
use BEAR\Resource\ResourceObject;

class User extends ResourceObject
{
    /**
     * @Link(rel="profile", href="/profile{?id}")
     * @Embed(rel="website", src="app://self/website{?id}")
     * @Embed(rel="contact", src="app://self/contact{?id}")
     */
    public function onGet($id)
    {
        $this['id'] = $id;
        $this['name'] = 'Akihito Koriyama';

        return $this;
    }
}
