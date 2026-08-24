<?php

declare(strict_types=1);

namespace BEAR\Package\Module;

use BEAR\Package\Exception\DeclaredWriteDirException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class WriteDirsTest extends TestCase
{
    /** The declaration is compiled in and read wherever the build runs, so it names one place. */
    public function testAbsolutePathsAreCarried(): void
    {
        $dirs = new WriteDirs('/var/app/tmp', '/var/log/app');

        $this->assertSame('/var/app/tmp', $dirs->tmpDir);
        $this->assertSame('/var/log/app', $dirs->logDir);
    }

    /** One directory, one spelling: a marker records this and the pack compares it. */
    public function testTrailingSeparatorsAreDropped(): void
    {
        $dirs = new WriteDirs('/var/app/tmp/', '/var/log/app//');

        $this->assertSame('/var/app/tmp', $dirs->tmpDir);
        $this->assertSame('/var/log/app', $dirs->logDir);
    }

    /** An application inside an archive is named through a stream, and so is where it writes. */
    public function testStreamPathsAreAbsolute(): void
    {
        $dirs = new WriteDirs('s3://bucket/tmp', 's3://bucket/log');

        $this->assertSame('s3://bucket/tmp', $dirs->tmpDir);
    }

    /** A root is all separator, so trimming it away would leave nothing to name. */
    #[DataProvider('rootProvider')]
    public function testARootIsKeptWhole(string $root): void
    {
        $dirs = new WriteDirs($root, $root);

        $this->assertSame($root, $dirs->tmpDir);
    }

    /** @return array<string, array{0: string}> */
    public static function rootProvider(): array
    {
        return ['posix' => ['/'], 'drive' => ['C:\\'], 'unc' => ['\\\\share']];
    }

    /**
     * A relative path names whatever directory the process started from, which a compile, a
     * request under fpm and a CLI run each answer differently - and the pack cannot tell that
     * it points inside the archive.
     */
    #[DataProvider('unusableProvider')]
    public function testAPathNoDeploymentCanResolveIsRefused(string $tmpDir, string $logDir): void
    {
        $this->expectException(DeclaredWriteDirException::class);
        new WriteDirs($tmpDir, $logDir);
    }

    /** @return array<string, array{0: string, 1: string}> */
    public static function unusableProvider(): array
    {
        return [
            'relative tmp' => ['var/tmp', '/var/log/app'],
            'relative log' => ['/var/app/tmp', 'var/log'],
            'empty tmp' => ['', '/var/log/app'],
            'empty log' => ['/var/app/tmp', ''],
            'dot relative' => ['./var/tmp', '/var/log/app'],
        ];
    }
}
