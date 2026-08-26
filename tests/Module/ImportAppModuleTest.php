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
     * An imported application is a separate application: its own module tree answers where it
     * writes, so nothing of it lands under the host's directories.
     */
    public function testImportAppWritesNothingUnderTheHost(): void
    {
        $hostWrite = sys_get_temp_dir() . '/bear-import-write-' . uniqid();
        $injector = new Injector($this->module([new ImportApp('bar', 'Import\HelloWorld', 'app')], $hostWrite));

        $resource = $injector->getInstance(ResourceInterface::class);
        assert($resource instanceof ResourceInterface);
        $resource->get('page://bar/index');

        $this->assertDirectoryDoesNotExist($hostWrite . '/tmp/Import');
        $this->assertDirectoryDoesNotExist($hostWrite . '/log/Import');
    }

    /** @param list<ImportApp> $imports */
    private function module(array $imports, string|null $hostWrite = null): AbstractModule
    {
        $appDir = dirname(__DIR__) . '/Fake/fake-app';
        $meta = $hostWrite === null
            ? new Meta('FakeVendor\HelloWorld', 'app', $appDir)
            : new Meta('FakeVendor\HelloWorld', 'app', $appDir, $hostWrite . '/tmp', $hostWrite . '/log');
        $module = new ResourceModule('FakeVendor\HelloWorld');
        $module->override(new AppMetaModule($meta, new WriteModule($meta, 'app')));
        $module->override(new ImportAppModule($imports));

        return $module;
    }
}
