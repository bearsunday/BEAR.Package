<?php

declare(strict_types=1);

namespace BEAR\Package\Module;

use BEAR\AppMeta\Meta;
use BEAR\Package\Module\Import\ImportApp;
use BEAR\Resource\Module\ResourceModule;
use BEAR\Resource\ResourceInterface;
use FakeVendor\HelloWorld\Resource\Page\Index;
use Import\HelloWorld\Resource\Page\Index as ImportIndex;
use PHPUnit\Framework\TestCase;
use Ray\Di\AbstractModule;
use Ray\Di\Injector;

use function assert;
use function dirname;
use function sys_get_temp_dir;
use function uniqid;

class ImportAppModuleTest extends TestCase
{
    public function testModule(): void
    {
        $injector = new Injector($this->module([new ImportApp('foo', 'Import\HelloWorld', 'app')]));
        $resource = $injector->getInstance(ResourceInterface::class);
        assert($resource instanceof ResourceInterface);
        $ro = $resource->get('page://self/index', ['name' => 'BEAR']);
        assert($ro instanceof Index);
        $this->assertSame('Hello BEAR', $ro->body['greeting']);
        // import
        $ro = $resource->get('page://foo/index', ['name' => 'Sunday']);
        assert($ro instanceof ImportIndex);
        $this->assertSame('Konichiwa Sunday', $ro->body['greeting']);
    }

    /**
     * An imported application is a separate application with its own Meta, and it writes under the
     * host's tmp and log: the write directory comes from the container, not from the declaration.
     */
    public function testImportAppWritesUnderTheHostWriteDir(): void
    {
        $writeDir = sys_get_temp_dir() . '/bear-import-write-' . uniqid();
        $injector = new Injector($this->module([new ImportApp('bar', 'Import\HelloWorld', 'app')], $writeDir));

        $resource = $injector->getInstance(ResourceInterface::class);
        assert($resource instanceof ResourceInterface);
        $resource->get('page://bar/index');

        $hostTmp = $writeDir . '/FakeVendor/HelloWorld/app/tmp';
        $hostLog = $writeDir . '/FakeVendor/HelloWorld/app/log';
        $this->assertDirectoryExists($hostTmp . '/Import/HelloWorld/app/tmp');
        $this->assertDirectoryExists($hostLog . '/Import/HelloWorld/app/log');
    }

    /**
     * @param list<ImportApp>       $imports
     * @param non-empty-string|null $writeDir
     */
    private function module(array $imports, string|null $writeDir = null): AbstractModule
    {
        $meta = Meta::create('FakeVendor\HelloWorld', 'app', dirname(__DIR__) . '/Fake/fake-app', $writeDir);
        $module = new ResourceModule('FakeVendor\HelloWorld');
        $module->override(new AppMetaModule($meta));
        $module->override(new ImportAppModule($imports));

        return $module;
    }
}
