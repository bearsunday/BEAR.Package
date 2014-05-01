<?php

namespace BEAR\Package\Provide\ResourceView;

class SchemeUriMapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SchemeUriMapper
     */
    protected $uriMapper;

    public function setUp()
    {
        $this->uriMapper = new SchemeUriMapper;
    }

    public function testNew()
    {
        $this->assertInstanceOf('BEAR\Package\Provide\ResourceView\SchemeUriMapper', $this->uriMapper);
    }

    public function testMap()
    {
        $uri = $this->uriMapper->map('app/blog/posts');
        $this->assertSame('app://self/blog/posts', $uri);
    }

    public function testMapWithServer()
    {
        $_SERVER['HTTP_HOST'] = 'http://example.com';
        $uri = $this->uriMapper->map('app/blog/posts');
        $this->assertSame('app://self/blog/posts', $uri);
        unset($_SERVER['HTTP_HOST']);
    }

    public function testReverseMap()
    {
        $href = $this->uriMapper->reverseMap('https://localhost:8080', 'app://blog/posts');
        $this->assertSame('https://localhost:8080/app/posts/', $href);

    }
}
