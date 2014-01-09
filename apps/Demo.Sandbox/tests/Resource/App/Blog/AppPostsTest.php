<?php
namespace Sandbox\tests\Resource\App\Blog;

use Ray\Di\Injector;

class AppPostsTest extends \PHPUnit_Extensions_Database_TestCase
{
    /**
     * Resource client
     *
     * @var \BEAR\Resource\Resource
     */
    private $resource;

    /**
     * @return \PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    public function getConnection()
    {
        $pdo = require $GLOBALS['APP_DIR'] . '/tests/scripts/db.php';

        return $this->createDefaultDBConnection($pdo, 'sqlite');
    }

    /**
     * @return \PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet()
    {
        $seed = $this->createFlatXmlDataSet($GLOBALS['APP_DIR'] . '/tests/mock/seed.xml');
        return $seed;
    }

    protected function setUp()
    {
        parent::setUp();
        $this->resource = clone $GLOBALS['RESOURCE'];
    }

    /**
     * page://self/blog/posts
     *
     * @test
     */
    public function resource()
    {
        // resource request
        $resource = $this->resource->get->uri('app://self/blog/posts')->eager->request();
        $this->assertSame(200, $resource->code);

        return $resource;
    }

    /**
     * What does page have ?
     *
     * @depends resource
     */
    public function graph($resource)
    {
    }

    /**
     * Type ?
     *
     * @depends resource
     * @test
     */
    public function type($resource)
    {
        $this->assertInternalType('array', $resource->body);
    }

    /**
     * Renderable ?
     *
     * @depends resource
     * @test
     */
    public function render($resource)
    {
        $html = (string)$resource;
        $this->assertInternalType('string', $html);
    }

    /**
     * @test
     */
    public function post()
    {
        // inc 1
        $before = $this->getConnection()->getRowCount('posts');
        $this->resource
            ->post
            ->uri('app://self/blog/posts')
            ->withQuery(['title' => 'test_title', 'body' => 'test_body'])
            ->eager
            ->request();
        $this->assertEquals($before + 1, $this->getConnection()->getRowCount('posts'), "failed to add post");

        // new post
        $entries = $this->resource->get->uri('app://self/blog/posts')->withQuery([])->eager->request()->body;
        $body = array_pop($entries);

        return $body;
    }

    /**
     * @test
     * @depends post
     */
    public function postData($body)
    {
        $this->assertEquals('test_title', $body['title']);
        $this->assertEquals('test_body', $body['body']);
    }

    /**
     * @test
     */
    public function delete()
    {
        // dec 1
        $before = $this->getConnection()->getRowCount('posts');
        $this->resource->delete->uri('app://self/blog/posts')->withQuery(['id' => 1])->eager->request();
        $this->assertEquals($before - 1, $this->getConnection()->getRowCount('posts'), "failed to delete post");
    }
}
