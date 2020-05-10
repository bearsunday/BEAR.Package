<?php

declare(strict_types=1);

namespace BEAR\Package;

use BEAR\Sunday\Extension\Application\AppInterface;
use FakeVendor\HelloWorld\Module\App;
use PHPUnit\Framework\TestCase;

class InjectorTest extends TestCase
{
    protected function setUp() : void
    {
        (new Unlink)->force(__DIR__ . '/script/var');
        parent::setUp();
    }

    public function testGetInstance()
    {
        $cmd = sprintf('php %s/script/ray-app.php', __DIR__);
        $exitCode = (new AsyncRun)([$cmd], __DIR__ . 'error.log');
        $this->assertSame(0, $exitCode);

        $injector = new Injector('FakeVendor\HelloWorld', 'app', __DIR__ . '/Fake/fake-app');
        /** @var App $app */
        $app = $injector->getInstance(AppInterface::class);
        $this->assertInstanceOf(AppInterface::class, $app);
        $this->assertSame(1, $app::$construct);

        $app = $injector->getInstance(AppInterface::class);
        $this->assertSame(1, $app::$construct);
    }

    public function testGetInstaceProd()
    {
        App::$construct = 0;
        $cmd = sprintf('php %s/script/ray-prod.php', __DIR__);
        $exitCode = (new AsyncRun)([$cmd], __DIR__ . 'error.log');
        $this->assertSame(0, $exitCode);

        $injector = new Injector('FakeVendor\HelloWorld', 'prod-app', __DIR__ . '/Fake/fake-app');
        /** @var App $app */
        $app = $injector->getInstance(AppInterface::class);
        $this->assertInstanceOf(AppInterface::class, $app);
        $this->assertSame(0, $app::$construct);

        $this->assertInstanceOf(AppInterface::class, $app);
        $this->assertSame(0, $app::$construct);
    }
}
