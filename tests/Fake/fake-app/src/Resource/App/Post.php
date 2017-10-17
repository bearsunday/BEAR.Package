<?php
/**
 * This file is part of the BEAR.Package package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace FakeVendor\HelloWorld\Resource\App;

use BEAR\Package\Annotation\ReturnCreatedResource;
use BEAR\Resource\Annotation\Link;
use BEAR\Resource\ResourceObject;
use BEAR\Sunday\Inject\ResourceInject;

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
            'name' => 'user_' . $id,
            '_links' => [
                'test' => ['href' => '/test']
            ]
        ];

        return $this;
    }

    /**
     * @ReturnCreatedResource
     */
    public function onPost($code = 201, $uri = '/post?id=10')
    {
        $this->code = $code;
        $this->headers['Location'] = $uri;

        return $this;
    }
}
