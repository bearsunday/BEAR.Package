<?php

declare(strict_types=1);

namespace BEAR\Package\Module;

use BEAR\AppMeta\Meta;
use BEAR\Package\Injector\PackageInjector;
use BEAR\Package\Module\Import\ImportApp;
use BEAR\Resource\Module\ResourceModule;
use BEAR\Resource\ResourceInterface;
use FakeVendor\HelloWorld\Resource\Page\Index;
use Import\HelloWorld\Resource\Page\Dirs;
use Import\HelloWorld\Resource\Page\Index as ImportIndex;
use PHPUnit\Framework\TestCase;
use Ray\Di\AbstractModule;
use Ray\Di\Injector;
use ReflectionProperty;

use function assert;
use function BEAR\Package\deleteFiles;
use function dirname;
use function str_replace;
use function sys_get_temp_dir;
use function uniqid;

class ImportAppModuleTest extends TestCase
{
    /**
     * The imported application boots from compiled scripts, so both its build and the injector
     * this process already holds are the first host's. Each test boots one of its own.
     */
    protected function setUp(): void
    {
        parent::setUp();

        (new ReflectionProperty(PackageInjector::class, 'instances'))->setValue(null, []);
        deleteFiles(dirname(__DIR__) . '/Fake/import-app/var');
    }

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
     * An imported application is an application: it writes in its own tree, where __wakeup() can
     * re-point it, and the host's declaration is not its business.
     */
    public function testImportAppWritesInItsOwnTree(): void
    {
        $host = sys_get_temp_dir() . '/bear-import-host-' . uniqid();
        $injector = new Injector($this->module([new ImportApp('bar', 'Import\HelloWorld', 'app')], $host . '/tmp', $host . '/log'));

        $resource = $injector->getInstance(ResourceInterface::class);
        assert($resource instanceof ResourceInterface);
        $ro = $resource->get('page://bar/dirs');
        assert($ro instanceof Dirs);

        // Meta spells appDir with the platform separator and its own tail with a slash
        $own = self::forwardSlashed(dirname(__DIR__) . '/Fake/import-app');
        $this->assertSame($own . '/var/tmp/app', self::forwardSlashed($ro->body['tmpDir']));
        $this->assertSame($own . '/var/log/app', self::forwardSlashed($ro->body['logDir']));
    }

    private static function forwardSlashed(string $path): string
    {
        return str_replace('\\', '/', $path);
    }

    /**
     * @param list<ImportApp>       $imports
     * @param non-empty-string|null $tmpDir
     * @param non-empty-string|null $logDir
     */
    private function module(array $imports, string|null $tmpDir = null, string|null $logDir = null): AbstractModule
    {
        $meta = new Meta('FakeVendor\HelloWorld', 'app', dirname(__DIR__) . '/Fake/fake-app');
        $chain = new ResourceModule('FakeVendor\HelloWorld');
        $chain->override(new ImportAppModule($imports));
        $declared = $tmpDir === null ? $chain : new ReadOnlyAppModule($tmpDir, $logDir, $chain);

        return new AppMetaModule($meta, $declared);
    }
}
