<?php
namespace MyVendor\MyProject\Resource\Page;

use BEAR\Package\AppInjector;
use BEAR\Resource\ResourceInterface;
use BEAR\Resource\ResourceObject;
use PHPUnit\Framework\TestCase;

class IndexTest extends TestCase
{
    /**
     * @var ResourceInterface
     */
    private $resource;

    protected function setUp()
    {
        parent::setUp();
        $this->resource = (new AppInjector('MyVendor\MyProject', 'app'))->getInstance(ResourceInterface::class);
    }

    public function testOnGet()
    {
        $index = $this->resource->uri('page://self/index')(['name' => 'BEAR.Sunday']);
        /* @var $index Index */
        $this->assertSame(200, $index->code);
        $this->assertSame('Hello BEAR.Sunday', $index['greeting']);

        return $index;
    }

    /**
     * @depends testOnGet
     */
    public function testView(ResourceObject $ro)
    {
        $json = json_decode((string) $ro);
        $this->assertSame('Hello BEAR.Sunday', $json->greeting);
    }
}
