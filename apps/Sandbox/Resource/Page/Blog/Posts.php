<?php
/**
 * App resource
 *
 * @package    Sandbox
 * @subpackage Resource
 */
namespace Sandbox\Resource\Page\Blog;

use BEAR\Sunday\Resource\AbstractPage as Page;
use BEAR\Sunday\Inject\ResourceInject;
use BEAR\Sunday\Annotation;
use Ray\Di\Di\Inject;
use BEAR\Sunday\Annotation\Cache;

/**
 * Blog index page
 *
 * @package    Sandbox
 * @subpackage Resource
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

    /**
     * @param int $id
     */
    public function onDelete($id)
    {
        // delete
        $this->resource
            ->delete
            ->uri('app://self/blog/posts')
            ->withQuery(['id' => $id])
            ->eager
            ->request();

        // redirect
        $this->headers['location'] = '/blog/posts';
        return $this;
    }
}
