<?php

declare(strict_types=1);

namespace BEAR\Package\Injector;

use PHPUnit\Framework\TestCase;

/** This package's layout under a write directory, in both directions. */
class AppDirsTest extends TestCase
{
    public function testWriteDirOfATmpDir(): void
    {
        $this->assertSame('/write', AppDirs::writeDirOf('My\App', '/write/My/App/prod-app/tmp'));
    }

    /** A tmp directory that does not follow the layout names no write directory. */
    public function testTmpDirOutsideTheLayout(): void
    {
        $this->assertNull(AppDirs::writeDirOf('My\App', '/app/var/tmp/prod-app'));
    }
}
