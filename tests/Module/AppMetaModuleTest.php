<?php

declare(strict_types=1);

namespace BEAR\Package\Module;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\Package\Annotation\AppDir;
use BEAR\Package\Annotation\WriteDir;
use BEAR\Package\Injector\AppDirs;
use BEAR\Resource\Annotation\AppName;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

use function dirname;
use function sys_get_temp_dir;
use function uniqid;

/** The bindings an application reads instead of finding these values for itself. */
class AppMetaModuleTest extends TestCase
{
    public function testMetaValuesAreBound(): void
    {
        $writeDir = sys_get_temp_dir() . '/bear-appmeta-' . uniqid();
        $appDir = dirname(__DIR__) . '/Fake/fake-app';
        $injector = new Injector(new AppMetaModule(AppDirs::meta('FakeVendor\HelloWorld', 'app', $appDir, $writeDir)));

        $this->assertSame('FakeVendor\HelloWorld', $injector->getInstance('', AppName::class));
        $this->assertSame($appDir, $injector->getInstance('', AppDir::class));
        $this->assertSame($writeDir, $injector->getInstance('', WriteDir::class));
        $this->assertInstanceOf(AbstractAppMeta::class, $injector->getInstance(AbstractAppMeta::class));
    }

    /** An application that writes under its own var/ has no write directory to name. */
    public function testWriteDirIsNullWithoutOne(): void
    {
        $injector = new Injector(new AppMetaModule(AppDirs::meta('FakeVendor\HelloWorld', 'app', dirname(__DIR__) . '/Fake/fake-app')));

        $this->assertNull($injector->getInstance('', WriteDir::class));
    }
}
