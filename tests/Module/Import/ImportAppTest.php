<?php

declare(strict_types=1);

namespace BEAR\Package\Module\Import;

use BEAR\AppMeta\Exception\AppNameException;
use PHPUnit\Framework\TestCase;

use function dirname;
use function realpath;
use function serialize;

class ImportAppTest extends TestCase
{
    public function testAppDirIsTheImportedApplicationRoot(): void
    {
        $importApp = new ImportApp('foo', 'Import\HelloWorld', 'app');
        $this->assertSame(realpath(dirname(__DIR__, 2) . '/Fake/import-app'), realpath($importApp->appDir()));
    }

    /** An unknown application name gets app-meta's own exception, not a reflection error. */
    public function testAppNameWithoutAnAppModule(): void
    {
        $importApp = new ImportApp('foo', 'No\Such\App', 'app');

        $this->expectException(AppNameException::class);
        $importApp->appDir();
    }

    /**
     * The compiled container carries this object, so a directory kept here would be the build's,
     * not the one the artifact runs from.
     */
    public function testCarriesNoDirectoryOfItsOwn(): void
    {
        $importApp = new ImportApp('foo', 'Import\HelloWorld', 'app');
        $this->assertStringNotContainsString($importApp->appDir(), serialize($importApp));
    }
}
