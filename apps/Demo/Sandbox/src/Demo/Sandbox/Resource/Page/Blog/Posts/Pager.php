<?php

namespace Demo\Sandbox\Resource\Page\Blog\Posts;

use BEAR\Resource\ResourceObject as Page;
use BEAR\Sunday\Inject\ResourceInject;

/**
 * Blog entry pager page
 */
class Pager extends Page
{
    use ResourceInject;

    /**
     * @var array
     */
    public $body = [
        'posts' => ''
    ];

    public function onGet()
    {
        $this['posts'] = $this->resource->get->uri('app://self/blog/posts/pager')->eager->request();

        return $this;
    }
}
