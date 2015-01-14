<?php

namespace MyVendor\MyApp\Resource\App;

use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\Annotation\Link;
use BEAR\Resource\ResourceObject;
use BEAR\Sunday\Inject\ResourceInject;

class User extends ResourceObject
{
    use ResourceInject;

    /**
     * @Link(rel="contact", href="/contact{?id}")
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
