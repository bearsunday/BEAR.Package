<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use ArrayObject;
use BEAR\Package\Exception\PartialWriteException;
use PHPUnit\Framework\TestCase;

use function chmod;
use function file_get_contents;
use function mkdir;
use function restore_error_handler;
use function rmdir;
use function set_error_handler;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;

class FilePutContentsTest extends TestCase
{
    private FilePutContents $filePutContents;
    private string $dir;

    protected function setUp(): void
    {
        /** @var ArrayObject<int, string> $overwritten */
        $overwritten = new ArrayObject();
        $this->filePutContents = new FilePutContents($overwritten);
        $this->dir = sys_get_temp_dir() . '/bear-file-put-' . uniqid('', true);
        mkdir($this->dir, 0777, true);
    }

    protected function tearDown(): void
    {
        chmod($this->dir, 0777);
        @unlink($this->dir . '/generated.php');
        @rmdir($this->dir);
    }

    public function testWritesAndTracksOverwrite(): void
    {
        $file = $this->dir . '/generated.php';
        ($this->filePutContents)($file, '<?php // one');
        $this->assertFalse($this->filePutContents->isOverwritten($file));

        ($this->filePutContents)($file, '<?php // two');
        $this->assertTrue($this->filePutContents->isOverwritten($file));
        $this->assertSame('<?php // two', file_get_contents($file));
    }

    /**
     * A short write is silent: file_put_contents() reports the byte count and no error.
     * Half a preload.php is a parse error on every boot, so the compile has to stop.
     */
    public function testRefusesToReportSuccessOnAWriteThatDidNotHappen(): void
    {
        chmod($this->dir, 0555);
        set_error_handler(static fn (): bool => true);
        try {
            $this->expectException(PartialWriteException::class);
            $this->expectExceptionMessage('Wrote 0 of 12 bytes');
            ($this->filePutContents)($this->dir . '/generated.php', '<?php // one');
        } finally {
            restore_error_handler();
        }
    }
}
