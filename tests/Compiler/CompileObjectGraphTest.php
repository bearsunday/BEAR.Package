<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use ArrayObject;
use PHPUnit\Framework\TestCase;
use Ray\Di\AbstractModule;

use function chmod;
use function file_put_contents;
use function glob;
use function mkdir;
use function rmdir;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;

use const PHP_OS_FAMILY;

class CompileObjectGraphTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/bear-object-graph-' . uniqid('', true);
        mkdir($this->dir, 0777, true);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->dir . '/*') ?: [] as $file) {
            unlink($file);
        }

        rmdir($this->dir);
    }

    public function testRendersSvgAndReturnsAPathThatExists(): void
    {
        $file = $this->compile($this->fakeDot('touch "$4"', 'type nul > %4'));

        $this->assertSame($this->dir . '/module.svg', $file);
        $this->assertFileExists($file);
    }

    public function testFallsBackToTheDotSourceWhenGraphvizIsAbsent(): void
    {
        $file = $this->compile(new DotCommand('bear-package-no-such-dot'));

        $this->assertSame($this->dir . '/module.dot', $file);
        $this->assertFileExists($file);
    }

    public function testKeepsTheDotSourceWhenDotRunsButFails(): void
    {
        $file = $this->compile($this->fakeDot('exit 1', 'exit /b 1'));

        $this->assertSame($this->dir . '/module.dot', $file);
        $this->assertFileDoesNotExist($this->dir . '/module.svg');
    }

    /**
     * A graphviz that behaves as the body says, written for the running OS so that both
     * outcomes are reachable without graphviz. `$4`/`%4` is the `-o` target of
     * `dot -Tsvg <dotFile> -o <svgFile>`.
     */
    private function fakeDot(string $shell, string $batch): DotCommand
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $batchFile = $this->dir . '/fake-dot.bat';
            file_put_contents($batchFile, "@echo off\r\n" . $batch . "\r\n");

            return new DotCommand($batchFile);
        }

        $shellFile = $this->dir . '/fake-dot';
        file_put_contents($shellFile, "#!/bin/sh\n" . $shell . "\n");
        chmod($shellFile, 0755);

        return new DotCommand($shellFile);
    }

    private function compile(DotCommand $dotCommand): string
    {
        /** @var ArrayObject<int, string> $overwritten */
        $overwritten = new ArrayObject();
        $compileObjectGraph = new CompileObjectGraph(new FilePutContents($overwritten), $this->dir, $dotCommand);

        return $compileObjectGraph($this->module());
    }

    private function module(): AbstractModule
    {
        return new class extends AbstractModule {
            protected function configure(): void
            {
                $this->bind(FilePutContents::class);
            }
        };
    }
}
