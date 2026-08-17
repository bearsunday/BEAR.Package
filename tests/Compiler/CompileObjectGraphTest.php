<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use ArrayObject;
use PHPUnit\Framework\TestCase;
use Ray\Di\AbstractModule;

use function dirname;
use function mkdir;
use function rmdir;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;

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
        @unlink($this->dir . '/module.dot');
        @unlink($this->dir . '/module.svg');
        @rmdir($this->dir);
    }

    public function testRendersSvgAndReturnsAPathThatExists(): void
    {
        $file = $this->compile(new DotCommand(dirname(__DIR__) . '/Fake/bin/fake-dot'));

        $this->assertSame($this->dir . '/module.svg', $file);
        $this->assertFileExists($file);
    }

    public function testFallsBackToTheDotSourceWhenGraphvizIsAbsent(): void
    {
        $file = $this->compile(new DotCommand('bear-package-no-such-dot'));

        $this->assertSame($this->dir . '/module.dot', $file);
        $this->assertFileExists($file);
    }

    /** 'false' is a command that exists and exits non-zero: a graphviz that runs and renders nothing. */
    public function testKeepsTheDotSourceWhenDotRunsButFails(): void
    {
        $file = $this->compile(new DotCommand('false'));

        $this->assertSame($this->dir . '/module.dot', $file);
        $this->assertFileDoesNotExist($this->dir . '/module.svg');
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
