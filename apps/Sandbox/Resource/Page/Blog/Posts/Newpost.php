<?php
/**
 * App resource
 *
 * @package    Sandbox
 * @subpackage Resource
 */
namespace Sandbox\Resource\Page\Blog\Posts;

use BEAR\Resource\Resource;
use BEAR\Sunday\Resource\AbstractPage as Page;
use BEAR\Sunday\Inject\ResourceInject;
use Ray\Di\Di\Inject;
use BEAR\Resource\Link;
use BEAR\Sunday\Annotation\Form;

/**
 * New post page
 *
 * @package    Sandbox
 * @subpackage Resource
 */
class Newpost extends Page
{
    use ResourceInject;

    /**
     * @var array
     */
    public $body = [
        'errors' => ['title' => '', 'body' => ''],
        'submit' => ['title' => '', 'body' => ''],
        'code' => 200
    ];

    /**
     * @var array
     */
    public $links = [
        'back' => [Link::HREF => 'page://self/blog/posts']
    ];

    /**
     * @return Newpost
     */
    public function onGet()
    {
        return $this;
    }

    /**
     * @param string $title
     * @param string $body
     *
     * @Form
     */
    public function onPost($title, $body)
    {
        // create post
        $response = $this->resource
            ->post
            ->uri('app://self/blog/posts')
            ->withQuery(['title' => $title, 'body' => $body])
            ->eager->request();

        $this['code'] = $response->code;
        $this->links += $response->links;
        // redirect
//      $this->code = 303;
//      $this->headers = ['Location' => '/blog/posts'];
        return $this;
    }
}
