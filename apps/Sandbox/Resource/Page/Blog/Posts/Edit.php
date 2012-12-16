<?php
/**
 * @package    Sandbox
 * @subpackage Resource
 */
namespace Sandbox\Resource\Page\Blog\Posts;

use BEAR\Resource\Resource;
use BEAR\Sunday\Resource\AbstractPage as Page;
use BEAR\Sunday\Inject\ResourceInject;
use Ray\Di\Di\Inject;
use BEAR\Sunday\Annotation\Form;
/**
 * Edit post page
 *
 * @package    Sandbox
 * @subpackage Resource
 */
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
     * @param $int id
     */
    public function onGet($id)
    {
        $this['submit'] = $this->resource->get->uri('app://self/blog/posts')->withQuery(['id' => $id])->eager->request()->body;
        $this['id'] = $id;
        return $this;
    }

    /**
     * @param int    $id
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
            ->eager->request();

        // redirect
        $this->code = 303;
        $this->headers = ['Location' => '/blog/posts'];
        return $this;
    }
}
