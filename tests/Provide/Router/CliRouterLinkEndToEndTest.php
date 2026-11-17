<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Router;

use BEAR\AppMeta\Meta;
use BEAR\Package\Context\CliModule;
use BEAR\Package\Module\AppMetaModule;
use BEAR\Package\Module\WriteModule;
use BEAR\Resource\Method;
use BEAR\Resource\ResourceInterface;
use BEAR\Sunday\Extension\Router\RouterInterface;
use FakeVendor\HelloWorld\Module\AppModule;
use FakeVendor\HelloWorld\Module\ProdModule;
use FakeVendor\HelloWorld\Resource\Page\LinkedFriend;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Ray\Di\AbstractModule;
use Ray\Di\Injector;
use Ray\PsrCacheModule\Annotation\Shared;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

use function assert;

/**
 * End-to-end: a CLI argv URI carrying a #link= fragment is routed by CliRouter and the
 * resulting request follows that #[Link] exactly once, the same way bin/app.php's
 * Bootstrap dispatches a real request.
 */
class CliRouterLinkEndToEndTest extends TestCase
{
    public function testCliRequestWithFragmentFollowsLinkOnce(): void
    {
        $meta = new Meta('FakeVendor\HelloWorld');
        $module = new AppMetaModule($meta, new ProdModule(new CliModule(new AppModule(new WriteModule($meta, 'prod-cli-app')))));
        $module->override(new class extends AbstractModule {
            protected function configure(): void
            {
                $this->bind(CacheItemPoolInterface::class)->annotatedWith(Shared::class)->to(FilesystemAdapter::class);
            }
        });
        $injector = new Injector($module, __DIR__ . '/../../tmp');
        $router = $injector->getInstance(RouterInterface::class);
        assert($router instanceof RouterInterface);
        $resource = $injector->getInstance(ResourceInterface::class);
        assert($resource instanceof ResourceInterface);

        LinkedFriend::$callCount = 0;
        $globals = ['_GET' => [], '_POST' => []];
        $server = [
            'argc' => 3,
            'argv' => ['php', 'get', '/linked-user?id=1#link=self:friend'],
        ];
        $request = $router->match($globals, $server); // @phpstan-ignore-line
        $this->assertSame('page://self/linked-user#link=self:friend', $request->path);

        $ro = $resource->newRequest(Method::from($request->method), $request->path, $request->query)();

        // The response is the linked "friend" resource, not the original "user" resource, and it
        // was fetched exactly once.
        $this->assertSame(1, LinkedFriend::$callCount);
        $this->assertSame(['id' => 'f1', 'resource' => 'friend'], $ro->body);
    }
}
