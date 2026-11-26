<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Router;

use BEAR\AppMeta\Meta;
use BEAR\Package\Context\CliModule;
use BEAR\Package\Module\AppMetaModule;
use BEAR\Package\Module\ResourceObjectModule;
use BEAR\Package\Module\WriteModule;
use BEAR\Resource\Method;
use BEAR\Resource\ResourceInterface;
use BEAR\Sunday\Extension\Router\RouterInterface;
use FakeVendor\HelloWorld\Module\AppModule;
use FakeVendor\HelloWorld\Module\ProdModule;
use FakeVendor\HelloWorld\Resource\Page\LinkedFriend;
use PHPUnit\Framework\Attributes\DataProvider;
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
    /** @return array<string, array{string, string, string}> */
    public static function linkProvider(): array
    {
        return [
            'link' => ['/linked-user?id=1#link=self:friend', 'page://self/linked-user#link=self:friend', 'f1'],
            'link query' => ['/linked-user?id=1#link=self:friend?friend_id=f9', 'page://self/linked-user#link=self:friend?friend_id=f9', 'f9'],
        ];
    }

    #[DataProvider('linkProvider')]
    public function testCliRequestWithFragmentFollowsLinkOnce(string $uri, string $path, string $friendId): void
    {
        $meta = new Meta('FakeVendor\HelloWorld');
        $module = new AppMetaModule($meta, new ProdModule(new CliModule(new AppModule(new WriteModule($meta, 'prod-cli-app')))));
        $module->override(new class extends AbstractModule {
            protected function configure(): void
            {
                $this->bind(CacheItemPoolInterface::class)->annotatedWith(Shared::class)->to(FilesystemAdapter::class);
            }
        });
        $module->install(new ResourceObjectModule($meta->getResourceListGenerator()));
        $injector = new Injector($module, __DIR__ . '/../../tmp');
        $router = $injector->getInstance(RouterInterface::class);
        assert($router instanceof RouterInterface);
        $resource = $injector->getInstance(ResourceInterface::class);
        assert($resource instanceof ResourceInterface);

        LinkedFriend::$callCount = 0;
        $globals = ['_GET' => [], '_POST' => []];
        $server = [
            'argc' => 3,
            'argv' => ['php', 'get', $uri],
        ];
        $request = $router->match($globals, $server); // @phpstan-ignore-line
        $this->assertSame($path, $request->path);

        $ro = $resource->newRequest(Method::from($request->method), $request->path, $request->query)();

        $this->assertSame(1, LinkedFriend::$callCount);
        $this->assertSame(['id' => $friendId, 'resource' => 'friend'], $ro->body);
    }
}
