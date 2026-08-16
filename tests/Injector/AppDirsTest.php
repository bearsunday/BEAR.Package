<?php

declare(strict_types=1);

namespace BEAR\Package\Injector;

use PHPUnit\Framework\TestCase;

use function dirname;
use function sys_get_temp_dir;
use function uniqid;

/** This package's layout under a write directory, in both directions. */
class AppDirsTest extends TestCase
{
    /** tmpDirIn() computes what meta() creates, so a marker can be compared without making directories. */
    public function testTmpDirInMatchesWhatMetaHolds(): void
    {
        $writeDir = sys_get_temp_dir() . '/bear-appdirs-' . uniqid();
        $appDir = dirname(__DIR__) . '/Fake/fake-app';

        $this->assertSame(
            AppDirs::tmpDirIn('My\App', 'prod-app', $writeDir),
            AppDirs::meta('My\App', 'prod-app', $appDir, $writeDir)->tmpDir,
        );
    }

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
