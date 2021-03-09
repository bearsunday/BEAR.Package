<?php

declare(strict_types=1);

namespace MyVendor\MyProjecct\Resource\Page;

use BEAR\Package\Bootstrap;
use BEAR\Package\Injector;
use BEAR\Resource\ResourceInterface;
use MyVendor\MyProject\Resource\Page\Index;
use PHPUnit\Framework\TestCase;
use function dirname;

class IndexTest extends TestCase
{
    /**
     * @var ResourceInterface
     */
    private $resource;

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
