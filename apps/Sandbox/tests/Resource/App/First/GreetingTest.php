<?php
namespace Sandbox\tests\Resource\App\Blog;

use Sandbox\Module\TestModule;
use Ray\Di\Injector;

class GreetingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Resource client
     *
     * @var BEAR\Resource\Resource
     */
    private $resource;

    protected function setUp()
    {
        static $app;

        parent::setUp();
        if (!$app) {
            $injector = Injector::create([new TestModule]);
            $app = $injector->getInstance('BEAR\Sunday\Application\Context');
        }
        $this->resource = $app->resource;
    }

    /**
     * resource
     *
     * @test
     */
    public function resource()
    {
        // resource request
        $resource = $this->resource->get->uri('app://self/first/greeting')->withQuery(['name' => 'BEAR'])->eager->request();
        $this->assertSame(200, $resource->code);

        return $resource;
    }

    /**
     * Type ?
     *
     * @depends resource
     * @test
     */
    public function type($resource)
    {
        $this->assertInternalType('string', $resource->body);
    }

    /**
     * Renderable ?
     *
     * @depends resource
     * test
     */
    public function render($resource)
    {
        $html = (string)$resource;
        $this->assertInternalType('string', $html);
    }

    /**
     * @depends resource
     * @test
     */
    public function body($resource)
    {
        $this->assertSame('Hello, BEAR', $resource->body);
    }
}
