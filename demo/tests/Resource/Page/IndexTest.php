<?php

declare(strict_types=1);
/**
 * This file is part of the BEAR.Sunday package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace MyVendor\MyProjecct\Resource\Page;

use BEAR\Package\Bootstrap;
use BEAR\Resource\ResourceInterface;
use MyVendor\HelloWorld\AppModule;
use MyVendor\MyProject\Resource\Page\Index;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

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
        $page = $this->resource->uri('page://self/index')(['name' => 'World']);
        /** @var Index $page */
        $this->assertSame(200, $page->code);
        $this->assertSame('Hello World', $page->body['greeting']);
        $expectJson = '{
    "greeting": "Hello World"
}
';
        $this->assertSame($expectJson, (string) $page);
    }
}
