<?php

declare(strict_types=1);

namespace BEAR\Package;

use BEAR\AppMeta\Meta;
use BEAR\Package\Context\CliModule;
use BEAR\Package\Module\AppMetaModule;
use BEAR\Package\Module\WriteModule;
use BEAR\Sunday\Extension\Application\AppInterface;
use FakeVendor\HelloWorld\Module\AppModule;
use FakeVendor\HelloWorld\Module\ProdModule;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Ray\Di\AbstractModule;
use Ray\Di\Injector;
use Ray\PsrCacheModule\Annotation\Shared;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

use function serialize;
use function unserialize;

class NewAppTest extends TestCase
{
    public function testGetInstanceByHand(): AppInterface
    {
        $meta = new Meta('FakeVendor\HelloWorld');
        $module = new AppMetaModule($meta, new ProdModule(new CliModule(new AppModule(new WriteModule($meta, 'prod-cli-app')))));
        $module->override(new class extends AbstractModule{
            protected function configure(): void
            {
                $this->bind(CacheItemPoolInterface::class)->annotatedWith(Shared::class)->to(FilesystemAdapter::class);
            }
        });
        $app = (new Injector($module, __DIR__ . '/tmp'))->getInstance(AppInterface::class);
        $this->assertInstanceOf(AppInterface::class, $app);

        return $app;
    }

    #[Depends('testGetInstanceByHand')]
    public function testSerializable(AppInterface $app): void
    {
        $this->assertInstanceOf(AppInterface::class, unserialize(serialize(unserialize(serialize($app)))));
    }
}
