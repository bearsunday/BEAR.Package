<?php

declare(strict_types=1);

namespace BEAR\Package\Module\Import;

use PHPUnit\Framework\TestCase;

use function dirname;
use function serialize;

class ImportAppTest extends TestCase
{
    public function testAppDirIsTheImportedApplicationRoot(): void
    {
        $importApp = new ImportApp('foo', 'Import\HelloWorld', 'app');
        $this->assertSame(dirname(__DIR__, 2) . '/Fake/import-app', $importApp->appDir());
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
