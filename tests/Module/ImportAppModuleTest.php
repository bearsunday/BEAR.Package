<?php

declare(strict_types=1);

namespace BEAR\Package\Module;

use BEAR\Package\Module\Import\ImportApp;
use BEAR\Resource\Module\ResourceModule;
use BEAR\Resource\ResourceInterface;
use FakeVendor\HelloWorld\Resource\Page\Index;
use Import\HelloWorld\Resource\Page\Index as ImportIndex;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

use function assert;
use function sys_get_temp_dir;
use function uniqid;

class ImportAppModuleTest extends TestCase
{
    public function testModule(): void
    {
        $module = new ResourceModule('FakeVendor\HelloWorld');
        $module->override(new ImportAppModule([new ImportApp('foo', 'Import\HelloWorld', 'app')]));
        $injector = new Injector($module);
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

    /** An imported application is a separate application: its own Meta, its own writable base. */
    public function testImportAppWritesUnderItsWriteDir(): void
    {
        $writeDir = sys_get_temp_dir() . '/bear-import-write-' . uniqid();
        $module = new ResourceModule('FakeVendor\HelloWorld');
        $module->override(new ImportAppModule([new ImportApp('bar', 'Import\HelloWorld', 'app', $writeDir)]));
        $injector = new Injector($module);
        $resource = $injector->getInstance(ResourceInterface::class);
        assert($resource instanceof ResourceInterface);
        $resource->get('page://bar/index');
        $this->assertDirectoryExists($writeDir . '/Import/HelloWorld/app/tmp');
        $this->assertDirectoryExists($writeDir . '/Import/HelloWorld/app/log');
    }
}
