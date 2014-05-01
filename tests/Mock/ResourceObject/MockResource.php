<?php
namespace BEAR\Package\Mock\ResourceObject;

use BEAR\Resource\ResourceObject;

use BEAR\Sunday\Annotation\Cache;
use BEAR\Sunday\Annotation\CacheUpdate;

class MockResource extends ResourceObject
{
    public $body = [
        'greeting' => 'hello'
    ];

    /**
     * Get
     *
     * @Cache(10)
     *
     * @return array
     */
    public function onGet()
    {
        return microtime(true);
    }

    /**
     * Post
     *
     * @CacheUpdate
     */
    public function onPost()
    {
        return $this;
    }

}
