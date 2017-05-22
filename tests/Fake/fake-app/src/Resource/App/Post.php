<?php
namespace FakeVendor\HelloWorld\Resource\App;

use BEAR\Resource\Annotation\Link;
use BEAR\Resource\ResourceObject;
use BEAR\Sunday\Inject\ResourceInject;

class Post extends ResourceObject
{
    use ResourceInject;

    /**
     * @Link(rel="comment", href="/comments/{?id}")
     * @Link(rel="category", href="/category/{?id}")
     */
    public function onGet($id)
    {
        $this['id'] = $id;
        $this['name'] = 'user_'  .$id;

        return $this;
    }

    public function onPost()
    {
        $this->code = 201;
        $this->headers['Location'] = '/post?id=10';

        return $this;
    }
}
