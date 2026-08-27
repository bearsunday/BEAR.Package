<?php

declare(strict_types=1);

namespace BEAR\Package;

use PHPUnit\Framework\TestCase;

use function escapeshellarg;
use function exec;
use function implode;
use function sprintf;

use const PHP_BINARY;

class BearCompileBinTest extends TestCase
{
    private const BIN = __DIR__ . '/../bin/bear.compile';
    private const APP_DIR = __DIR__ . '/Fake/fake-app';

    public function testCompile(): void
    {
        $command = sprintf(
            '%s %s %s prod-cli-app %s 2>&1',
            escapeshellarg(PHP_BINARY),
            escapeshellarg(self::BIN),
            escapeshellarg('FakeVendor\HelloWorld'),
            escapeshellarg(self::APP_DIR),
        );
        exec($command, $output, $exitCode);
        $joined = implode("\n", $output);
        $this->assertSame(0, $exitCode, $joined);
        $this->assertStringContainsString('Deprecated: bear.compile', $joined);
    }

    public function testUsageOnWrongArgc(): void
    {
        exec(sprintf('%s %s 2>&1', escapeshellarg(PHP_BINARY), escapeshellarg(self::BIN)), $output, $exitCode);
        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('usage: bear.compile', implode("\n", $output));
    }
}
