<?php
namespace Sandbox\tests\Resource\App\Blog;

use Sandbox\App;
use Sandbox\Module\TestModule;
use Ray\Di\Injector;

class AppPostsTest extends \PHPUnit_Extensions_Database_TestCase
{
    /**
     * @return \PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    public function getConnection()
    {
        $pdo = require App::DIR . '/tests/scripts/db.php';
        return $this->createDefaultDBConnection($pdo, 'mysql');
    }

    /**
     * @return \PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet()
    {
        $seed = $this->createMySQLXMLDataSet(App::DIR . '/tests/seed.xml');
        return $seed;
    }

    /**
     * Resource client
     *
     * @var \BEAR\Resource\Resource
     */
    private $resource;

    protected function setUp()
    {
        parent::setUp();
        if (! $this->resource) {
            $this->resource = Injector::create([new TestModule])->getInstance('\BEAR\Resource\Resource');
        }
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
        $body = $this->resource
            ->get
            ->uri('app://self/blog/posts')
            ->withQuery(['id' => 4])
            ->eager
            ->request()->body;

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
        $this->resource
            ->delete
            ->uri('app://self/blog/posts')
            ->withQuery(['id' => 1])
            ->eager
            ->request();
        $this->assertEquals($before - 1, $this->getConnection()->getRowCount('posts'), "failed to delete post");
    }
}
