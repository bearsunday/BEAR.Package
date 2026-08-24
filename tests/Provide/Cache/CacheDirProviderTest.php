<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Cache;

use BEAR\AppMeta\Meta;
use BEAR\Package\Exception\DirectoryNotWritableException;
use PHPUnit\Framework\TestCase;

use function BEAR\Package\deleteFiles;
use function chmod;
use function dirname;
use function is_writable;
use function mkdir;
use function rmdir;
use function sys_get_temp_dir;
use function uniqid;

use const DIRECTORY_SEPARATOR;

class CacheDirProviderTest extends TestCase
{
    /** @var non-empty-string */
    private string $tmpDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tmpDir = sys_get_temp_dir() . '/bear-cache-dir-' . uniqid('', true);
        mkdir($this->tmpDir, 0777, true);
    }

    protected function tearDown(): void
    {
        chmod($this->tmpDir . '/cache', 0777);
        deleteFiles($this->tmpDir);

        parent::tearDown();
    }

    /** The directory is created on the way, under whatever spelling Meta answers. */
    public function testAnAbsentCacheDirectory(): void
    {
        $meta = $this->meta();

        $this->assertSame($meta->tmpDir . '/cache', (new CacheDirProvider($meta))->get());
    }

    /** Nothing has made tmpDir, so mkdir() has to reach through it. */
    public function testAnAbsentTmpDirectory(): void
    {
        $meta = $this->meta();
        deleteFiles($this->tmpDir);
        rmdir($this->tmpDir);

        $this->assertSame($meta->tmpDir . '/cache', (new CacheDirProvider($meta))->get());
    }

    /**
     * mkdir() answers false for a directory that is already there, so a present one that cannot be
     * written would be mistaken for a lost race and handed back unusable.
     */
    public function testAPresentCacheDirectoryThatCannotBeWritten(): void
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            $this->markTestSkipped('chmod does not restrict writing on Windows');
        }

        mkdir($this->tmpDir . '/cache', 0500, true);
        if (is_writable($this->tmpDir . '/cache')) {
            $this->markTestSkipped('this test user writes whatever the mode says');
        }

        $this->expectException(DirectoryNotWritableException::class);
        (new CacheDirProvider($this->meta()))->get();
    }

    private function meta(): Meta
    {
        return new Meta(
            'FakeVendor\HelloWorld',
            'prod-app',
            dirname(__DIR__, 2) . '/Fake/fake-app',
            $this->tmpDir,
            $this->tmpDir,
        );
    }
}
