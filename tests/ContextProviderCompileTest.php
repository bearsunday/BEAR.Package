<?php

declare(strict_types=1);

namespace BEAR\Package;

use BEAR\AppMeta\AppMeta;
use BEAR\Package\Context\CliModule;
use BEAR\Resource\ResourceInterface;
use BEAR\Sunday\Extension\Application\AppInterface;
use BEAR\Sunday\Inject\ResourceInject;
use FakeVendor\HelloWorld\Module\App;
use FakeVendor\HelloWorld\Module\AppModule;
use FakeVendor\HelloWorld\Module\ContextModule;
use FakeVendor\HelloWorld\Module\ProdModule;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

class ContextProviderTest extends TestCase
{
    public function estContextualProvider()
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
