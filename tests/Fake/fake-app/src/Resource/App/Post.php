<?php
namespace FakeVendor\HelloWorld\Resource\App;

use BEAR\Package\Annotation\Curies;
use BEAR\Package\Annotation\ReturnCreatedResource;
use BEAR\Resource\Annotation\Link;
use BEAR\Resource\ResourceObject;
use BEAR\Sunday\Inject\ResourceInject;

/**
 * @Curies(name="ht", href="http://api.example.com/docs/{rel}")
 */
class Post extends ResourceObject
{
    use ResourceInject;

    /**
     * @Link(rel="ht:comment", href="/comments/{?id}")
     * @Link(rel="ht:category", href="/category/{?id}")
     */
    public function onGet($id)
    {
        $this->body = [
            'id' => $id,
            'name' => 'user_'  .$id,
            '_links' => [
                'test' => ['href' => '/test']
            ]
        ];

        return $this;
    }

    /**
     * @ReturnCreatedResource
     */
    public function onPost()
    {
        $this->code = 201;
        $this->headers['Location'] = '/post?id=10';

        return $this;
    }
}
