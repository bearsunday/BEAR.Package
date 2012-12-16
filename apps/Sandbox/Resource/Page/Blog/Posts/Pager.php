<?php
/**
 * App resource
 *
 * @package    Sandbox
 * @subpackage Resource
 */
namespace Sandbox\Resource\Page\Blog\Posts;

use BEAR\Sunday\Resource\AbstractPage as Page;
use BEAR\Sunday\Inject\ResourceInject;
use Ray\Di\Di\Inject;

/**
 * Blog entry pager page
 *
 * @package    Sandbox
 * @subpackage Resource
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
        $this['posts'] = $this
            ->resource
            ->get
            ->uri('app://self/blog/posts/pager')
            ->eager
            ->request();
        return $this;
    }
}
