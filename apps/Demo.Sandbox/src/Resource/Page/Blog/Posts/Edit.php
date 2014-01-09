<?php

namespace Demo\Sandbox\Resource\Page\Blog\Posts;

use BEAR\Resource\ResourceObject as Page;
use BEAR\Sunday\Annotation\Form;
use BEAR\Sunday\Inject\ResourceInject;

class Edit extends Page
{
    use ResourceInject;

    /**
     * @var array
     */
    public $body = [
        'errors' => ['title' => '', 'body' => ''],
        'submit' => ['title' => '', 'body' => '']
    ];

    /**
     * @param $id
     */
    public function onGet($id)
    {
        $this['submit'] = $this->resource
            ->get
            ->uri('app://self/blog/posts')
            ->withQuery(['id' => $id])
            ->eager
            ->request()
            ->body;
        $this['id'] = $id;

        return $this;
    }

    /**
     * @param int $id
     * @param string $title
     * @param string $body
     *
     * @Form
     */
    public function onPut($id, $title, $body)
    {
        // create post
        $this->resource
            ->put
            ->uri('app://self/blog/posts')
            ->withQuery(['id' => $id, 'title' => $title, 'body' => $body])
            ->eager
            ->request();

        // redirect
        $this->code = 303;
        $this->headers = ['Location' => '/blog/posts'];

        return $this;
    }
}
