<?php
namespace Skeleton\Resource\Page;

use BEAR\Test\ResourceSetupTrait;

class IndexTest extends \PHPUnit_Framework_TestCase
{
    use ResourceSetupTrait;

    /**
     * page resource
     *
     * @test
     */
    public function resource()
    {
        // resource request
        $page = $this->resource->get->uri('page://self/index')->eager->request();
        $this->assertSame(200, $page->code);

        return $page;
    }

    /**
     * @depends resource
     */
    public function testBody($page)
    {
        $this->assertArrayHasKey('greeting', $page->body);
    }

    /**
     * Renderable ?
     *
     * @depends resource
     */
    public function testRenderable($page)
    {
        $html = (string)$page;
        $this->assertInternalType('string', $html);
    }

    /**
     * Html Rendered ?
     *
     * @depends resource
     */
    public function testRenderedHtml($page)
    {
        $html = (string)$page;
        $this->assertContains('</html>', $html);
    }

    /**
     * @covers Skeleton\Resource\Page\Index::onGet
     */
    public function testOnGet()
    {
        $page = $this->resource->get->uri('page://self/index')->withQuery(['name' => 'koriym'])->eager->request();
        $this->assertSame('Hello koriym', $page['greeting']);
    }
}
