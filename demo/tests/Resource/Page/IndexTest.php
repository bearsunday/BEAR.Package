<?php

declare(strict_types=1);
/**
 * This file is part of the BEAR.Package package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace MyVendor\MyProjecct\Resource\Page;

use BEAR\Package\Bootstrap;
use BEAR\Resource\ResourceInterface;
use MyVendor\MyProject\Resource\Page\Index;
use PHPUnit\Framework\TestCase;

class IndexTest extends TestCase
{
    /**
     * @var ResourceInterface
     */
    private $resource;

    public function setUp()
    {
        parent::setUp();
        $this->resource = (new Bootstrap)->getApp('MyVendor\MyProject', 'app')->resource;
    }

    public function testGet()
    {
        $ro = $this->resource->uri('ro://self/index')(['name' => 'World']);
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
