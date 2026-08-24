<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Error;

use BEAR\AppMeta\Meta;
use LogicException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

use function BEAR\Package\deleteFiles;
use function dirname;
use function file_get_contents;
use function is_dir;
use function is_link;
use function mkdir;
use function readlink;
use function rmdir;
use function sprintf;
use function str_replace;
use function sys_get_temp_dir;

class FileLogRefWriterTest extends TestCase
{
    private string $logDir;
    private FileLogRefWriter $writer;

    protected function setUp(): void
    {
        parent::setUp();

        $dir = sys_get_temp_dir() . '/BEAR.Package.FileLogRefWriterTest';
        ! is_dir($dir) && mkdir($dir, 0777, true);
        $appDir = dirname(__DIR__, 2) . '/Fake/fake-app';
        $meta = new Meta('FakeVendor\HelloWorld', 'app', $appDir, $dir, $dir);
        $this->logDir = $meta->logDir;
        $this->writer = new FileLogRefWriter($meta);
    }

    protected function tearDown(): void
    {
        deleteFiles($this->logDir);
        rmdir($this->logDir);

        parent::tearDown();
    }

    public function testWritesTheDetailUnderTheLogRef(): void
    {
        $logRef = new LogRef(new LogicException('msg'));

        $this->writer->write($logRef, 'the detail');

        $this->assertSame('the detail', file_get_contents($this->file($logRef)));
    }

    public function testLinksTheLastWrittenFile(): void
    {
        $logRef = new LogRef(new LogicException('msg'));

        $this->writer->write($logRef, 'the detail');

        $link = $this->logDir . '/last.logref.log';
        $this->assertTrue(is_link($link));
        $this->assertSame($this->file($logRef), self::forwardSlashed((string) readlink($link)));
    }

    /** symlink() refuses an existing name, so the link would otherwise be stuck on the first error seen. */
    public function testRepointsTheLinkAtTheNextError(): void
    {
        $first = new LogRef(new LogicException('first'));
        $second = new LogRef(new RuntimeException('second'));

        $this->writer->write($first, 'first detail');
        $this->writer->write($second, 'second detail');

        $this->assertSame($this->file($second), self::forwardSlashed((string) readlink($this->logDir . '/last.logref.log')));
    }

    private function file(LogRef $logRef): string
    {
        return self::forwardSlashed(sprintf('%s/logref.%s.log', $this->logDir, (string) $logRef));
    }

    /** A temp directory and a readlink() each spell the platform separator; a comparison needs one. */
    private static function forwardSlashed(string $path): string
    {
        return str_replace('\\', '/', $path);
    }
}
