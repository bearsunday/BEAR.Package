<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use BEAR\Package\Exception\DirectoryNotWritableException;
use BEAR\Package\FakeRecordingStep;
use BEAR\Sunday\Compile\CompileStepInterface;
use FakeVendor\HelloWorld\FakeAlphaStep;
use FakeVendor\HelloWorld\FakeBetaStep;
use FilesystemIterator;
use PHPUnit\Framework\TestCase;
use Ray\Di\AbstractModule;
use Ray\Di\Injector as RayInjector;
use Ray\Di\InjectorInterface;
use Ray\Di\MultiBinder;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

use function file_get_contents;
use function file_put_contents;
use function is_dir;
use function mkdir;
use function rmdir;
use function sort;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;

class CompileStepsTest extends TestCase
{
    /** @var non-empty-string */
    private string $appDir;

    protected function setUp(): void
    {
        $this->appDir = sys_get_temp_dir() . '/bear-steps-' . uniqid('', true);
    }

    protected function tearDown(): void
    {
        if (! is_dir($this->appDir)) {
            @unlink($this->appDir);

            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->appDir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
        );
        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }

        rmdir($this->appDir);
    }

    /** @param array<string, CompileStepInterface> $steps */
    private static function injector(array $steps): InjectorInterface
    {
        return new RayInjector(new class ($steps) extends AbstractModule {
            /** @param array<string, CompileStepInterface> $steps */
            public function __construct(private array $steps)
            {
                parent::__construct();
            }

            protected function configure(): void
            {
                $binder = MultiBinder::newInstance($this, CompileStepInterface::class);
                foreach ($this->steps as $key => $step) {
                    $binder->addBinding($key)->toInstance($step);
                }
            }
        });
    }

    public function testTheCallerHandsEachStepTheDirectoryItsBindingKeyNames(): void
    {
        $step = new FakeRecordingStep();

        $this->assertSame(['recorded' => 0], CompileSteps::run(self::injector(['recorded' => $step]), $this->appDir));
        $this->assertSame($this->appDir . '/var/build/recorded', $step->stepDir);
        $this->assertTrue($step->stepDirExisted, 'the step had to create its own directory');
    }

    /** Each fake writes its own $stepDir into every artifact, so the files say which directory they were handed. */
    public function testTwoStepsWriteUnderTheirOwnKeys(): void
    {
        $steps = ['alpha' => new FakeAlphaStep(), 'beta' => new FakeBetaStep()];
        $counts = CompileSteps::run(self::injector($steps), $this->appDir);

        $buildDir = $this->appDir . '/var/build';
        $this->assertSame(['alpha' => 2, 'beta' => 1], $counts);
        $this->assertSame($buildDir . '/alpha', file_get_contents($buildDir . '/alpha/alpha-1.txt'));
        $this->assertSame($buildDir . '/alpha', file_get_contents($buildDir . '/alpha/alpha-2.txt'));
        $this->assertSame($buildDir . '/beta', file_get_contents($buildDir . '/beta/beta-1.txt'));
    }

    /**
     * A step handed the last build's files writes a different set than the same sources would
     * from empty: Twig's cache skips a template it can already see loaded, Qiq keeps the first
     * root that claimed a name.
     */
    public function testASecondRunOverAPopulatedDirectoryWritesTheSameSet(): void
    {
        $steps = ['alpha' => new FakeAlphaStep()];
        $stepDir = $this->appDir . '/var/build/alpha';

        $first = CompileSteps::run(self::injector($steps), $this->appDir);
        file_put_contents($stepDir . '/stale.txt', 'left by an earlier build');
        mkdir($stepDir . '/nested');
        file_put_contents($stepDir . '/nested/stale.txt', 'left by an earlier build');
        $second = CompileSteps::run(self::injector($steps), $this->appDir);

        $this->assertSame($first, $second);
        $this->assertSame(['alpha-1.txt', 'alpha-2.txt'], self::entries($stepDir));
    }

    /** @return list<string> */
    private static function entries(string $dir): array
    {
        $names = [];
        /** @var SplFileInfo $file */
        foreach (new FilesystemIterator($dir, FilesystemIterator::SKIP_DOTS) as $file) {
            $names[] = $file->getFilename();
        }

        sort($names);

        return $names;
    }

    public function testNoStepsIsNoWork(): void
    {
        $this->assertSame([], CompileSteps::run(self::injector([]), $this->appDir));
        $this->assertDirectoryDoesNotExist($this->appDir . '/var/build');
    }

    /** A step whose directory cannot be created must not be handed a path it would write nowhere. */
    public function testStepDirectoryThatCannotBeCreated(): void
    {
        $step = new FakeRecordingStep();
        file_put_contents($this->appDir, 'not a directory');

        try {
            $this->expectException(DirectoryNotWritableException::class);
            CompileSteps::run(self::injector(['blocked' => $step]), $this->appDir);
        } finally {
            $this->assertNull($step->stepDir);
        }
    }
}
