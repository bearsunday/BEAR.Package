<?php

declare(strict_types=1);

namespace BEAR\Package\Injector;

use PHPUnit\Framework\TestCase;

use function BEAR\Package\deleteFiles;
use function file_put_contents;
use function json_encode;
use function mkdir;
use function sys_get_temp_dir;
use function uniqid;

/** A marker this version cannot read is a marker that is not there. */
class CompileMarkerTest extends TestCase
{
    /** @var non-empty-string */
    private string $scriptDir;

    protected function setUp(): void
    {
        $this->scriptDir = sys_get_temp_dir() . '/bear-marker-' . uniqid();
        mkdir($this->scriptDir, 0777, true);
    }

    protected function tearDown(): void
    {
        deleteFiles($this->scriptDir);
    }

    public function testWhatWasWrittenIsWhatIsRead(): void
    {
        CompileMarker::write($this->scriptDir, 'My\App', 'prod-app', '/write/My/App/prod-app/tmp');

        $record = CompileMarker::read($this->scriptDir);

        $this->assertInstanceOf(CompileRecord::class, $record);
        $this->assertSame('My\App', $record->appName);
        $this->assertSame('prod-app', $record->context);
        $this->assertSame('/write/My/App/prod-app/tmp', $record->tmpDir);
        $this->assertGreaterThan(0, $record->time);
    }

    /**
     * A marker of the previous shape claimed something else: its tmpDir was the directory a
     * boot had to be given, and the boot compared it. Reading it as one of these would let a
     * build compiled for a write directory keep using it unannounced.
     */
    public function testAMarkerOfThePreviousShapeIsNotRead(): void
    {
        file_put_contents(CompileMarker::path($this->scriptDir), (string) json_encode([
            'app' => 'My\App',
            'context' => 'prod-app',
            'tmpDir' => '/old/write/My/App/prod-app/tmp',
            'time' => 1700000000,
        ]));

        $this->assertNull(CompileMarker::read($this->scriptDir));
        $this->assertFalse(CompileMarker::matches($this->scriptDir, 'My\App', 'prod-app'));
    }

    public function testAMarkerThatIsNotJson(): void
    {
        $this->marker('/write/My/App/prod-app/tmp');

        $this->assertNull(CompileMarker::read($this->scriptDir));
    }

    /** The 1.22 marker was a text file, so an upgraded deployment recompiles once. */
    public function testAMarkerMissingTheFieldsThisVersionNeeds(): void
    {
        $this->marker('{"context":"prod-app","tmpDir":"/write/My/App/prod-app/tmp"}');

        $this->assertNull(CompileMarker::read($this->scriptDir));
    }

    public function testAMarkerWithAnEmptyField(): void
    {
        $this->marker('{"app":"","context":"prod-app","tmpDir":"/write/My/App/prod-app/tmp"}');

        $this->assertNull(CompileMarker::read($this->scriptDir));
    }

    public function testAMarkerOfAnotherApplicationIsNotThisApplicationsBuild(): void
    {
        CompileMarker::write($this->scriptDir, 'My\App', 'prod-app', '/write/My/App/prod-app/tmp');

        $this->assertTrue(CompileMarker::matches($this->scriptDir, 'My\App', 'prod-app'));
        $this->assertFalse(CompileMarker::matches($this->scriptDir, 'Other\App', 'prod-app'));
    }

    public function testAMarkerOfAnotherContextIsNotThisContextsBuild(): void
    {
        CompileMarker::write($this->scriptDir, 'My\App', 'prod-app', '/write/My/App/prod-app/tmp');

        $this->assertTrue(CompileMarker::matches($this->scriptDir, 'My\App', 'prod-app'));
        $this->assertFalse(CompileMarker::matches($this->scriptDir, 'My\App', 'prod-hal-app'));
    }

    private function marker(string $content): void
    {
        file_put_contents(CompileMarker::path($this->scriptDir), $content);
    }
}
