<?php

declare(strict_types=1);

namespace MyVendor\MyProject\Resource\Page;

use BEAR\Package\Injector;
use BEAR\Resource\ResourceInterface;
use PHPUnit\Framework\TestCase;
use function dirname;

class IndexTest extends TestCase
{
    private ResourceInterface $resource;

    protected function setUp(): void
    {
        parent::setUp();
        $injector = Injector::getInstance('MyVendor\MyProject', 'app', dirname(__DIR__, 3));
        $this->resource = $injector->getInstance(ResourceInterface::class);
    }

    public function testGet()
    {
        $ro = $this->resource->uri('page://self/index')(['name' => 'World']);
        /* @var Index $ro */
        $this->assertSame(200, $ro->code);
        $this->assertSame('Hello World', $ro->body['greeting']);
        $expectJson = '{
    "greeting": "Hello World"
}
';
        $this->assertSame($expectJson, (string) $ro);
    }
}
