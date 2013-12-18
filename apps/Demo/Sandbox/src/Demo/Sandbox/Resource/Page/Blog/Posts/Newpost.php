<?php

namespace Demo\Sandbox\Resource\Page\Blog\Posts;

use BEAR\Resource\ResourceObject as Page;
use BEAR\Resource\Link;
use BEAR\Sunday\Annotation\Form;
use BEAR\Sunday\Inject\ResourceInject;

/**
 * New post page
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
        $response = $this
            ->resource
            ->post
            ->uri('app://self/blog/posts')
            ->withQuery(
                ['title' => $title, 'body' => $body]
            )
            ->eager
            ->request();

        $this->code = $this['code'] = $response->code;
        $this->links += $response->links;

        return $this;
    }
}
