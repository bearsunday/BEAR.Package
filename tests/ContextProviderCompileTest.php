<?php

declare(strict_types=1);

namespace BEAR\Package;

use BEAR\Resource\ResourceInterface;
use PHPUnit\Framework\TestCase;

class ContextProviderCompileTest extends TestCase
{
    public function testContextualProvider()
    {
        /** @var ResourceInterface $resource */
        $resource = (new AppInjector('FakeVendor\HelloWorld', 'prod-context-cli-app'))->getInstance(ResourceInterface::class);
        $ro = $resource->uri('page://self/context')();
        $this->assertSame(['a' => 'user', 'b' => 'job'], $ro->body);
    }

    public function testCachedContextualProvider()
    {
        (new Bootstrap)->getApp('FakeVendor\HelloWorld', 'prod-context-cli-app');
        $app = (new Bootstrap)->getApp('FakeVendor\HelloWorld', 'prod-context-cli-app');
        $ro = $app->resource->uri('page://self/context')();
        $this->assertSame(['a' => 'user', 'b' => 'job'], $ro->body);
    }
}
