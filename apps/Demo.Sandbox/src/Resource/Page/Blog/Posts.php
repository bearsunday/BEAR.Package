<?php

namespace Demo\Sandbox\Resource\Page\Blog;

use BEAR\Resource\ResourceObject as Page;
use BEAR\Sunday\Inject\ResourceInject;
use BEAR\Sunday\Annotation;
use BEAR\Sunday\Annotation\Cache;

/**
 * Blog index page
 *
 * Blog entries listed with edit/delete button
 */
class Posts extends Page
{
    use ResourceInject;

    /**
     * @var array
     */
    public $body = [
        'posts' => ''
    ];

    /**
     * @Cache
     * @internal Cache "request", not the result of request. never changed.
     */
    public function onGet()
    {
        $this['posts'] = $this->resource
            ->get
            ->uri('app://self/blog/posts')
            ->request();

        return $this;
    }
}
