<?php

declare(strict_types=1);

namespace BEAR\Package;

use BEAR\Resource\ResourceInterface;
use PHPUnit\Framework\TestCase;

use function assert;

class ContextProviderCompileTest extends TestCase
{
    public function testContextualProvider(): void
    {
        $resource = (new AppInjector('FakeVendor\HelloWorld', 'prod-context-cli-app'))->getInstance(ResourceInterface::class);
        assert($resource instanceof ResourceInterface);
        $ro = $resource->uri('page://self/context')();
        $this->assertSame(['a' => 'user', 'b' => 'job'], $ro->body);
    }

    public function testCachedContextualProvider(): void
    {
        (new Bootstrap())->getApp('FakeVendor\HelloWorld', 'prod-context-cli-app');
        $app = (new Bootstrap())->getApp('FakeVendor\HelloWorld', 'prod-context-cli-app');
        $ro = $app->resource->uri('page://self/context')();
        $this->assertSame(['a' => 'user', 'b' => 'job'], $ro->body);
    }
}
