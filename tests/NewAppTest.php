<?php

declare(strict_types=1);

namespace BEAR\Package;

use BEAR\AppMeta\Meta;
use BEAR\Package\Context\CliModule;
use BEAR\Package\Module\AppMetaModule as PackageAppMetaModule;
use BEAR\Sunday\Extension\Application\AppInterface;
use FakeVendor\HelloWorld\Module\AppModule;
use FakeVendor\HelloWorld\Module\ProdModule;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

use function serialize;
use function unserialize;

class NewAppTest extends TestCase
{
    public function testGetInstanceByHand(): AppInterface
    {
        $app = (new Injector(new PackageAppMetaModule(new Meta('FakeVendor\HelloWorld'), new ProdModule(new CliModule(new AppModule()))), __DIR__ . '/tmp'))->getInstance(AppInterface::class);
        $this->assertInstanceOf(AppInterface::class, $app);

        return $app;
    }

    /**
     * @depends testGetInstanceByHand
     */
    public function testSerializable(AppInterface $app): void
    {
        $this->assertInstanceOf(AppInterface::class, unserialize(serialize($app)));
    }
}
