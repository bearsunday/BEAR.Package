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

use function assert as assertAlias;

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
}
