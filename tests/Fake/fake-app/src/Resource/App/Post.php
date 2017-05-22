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
        $this['post_id'] = $id;

        return $this;
    }

    public function onPost()
    {
        $this->code = 201;
        $this->headers['Location'] = '/post?id=10';
        $this->body = $this->resource->uri('app://self' . $this->headers['Location'])->eager->request()->body;

        return $this;
    }
}
