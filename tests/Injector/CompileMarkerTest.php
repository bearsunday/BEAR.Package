<?php

declare(strict_types=1);

namespace BEAR\Package\Injector;

use PHPUnit\Framework\TestCase;

use function BEAR\Package\deleteFiles;
use function file_put_contents;
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
        CompileMarker::write($this->scriptDir, 'My\App', 'prod-app');

        $record = CompileMarker::read($this->scriptDir);

        $this->assertInstanceOf(CompileRecord::class, $record);
        $this->assertSame('My\App', $record->appName);
        $this->assertSame('prod-app', $record->context);
        $this->assertGreaterThan(0, $record->time);
    }

    public function testAMarkerThatIsNotJson(): void
    {
        $this->marker('not json at all');

        $this->assertNull(CompileMarker::read($this->scriptDir));
    }

    /** The 1.22 marker was a text file, so an upgraded deployment recompiles once. */
    public function testAMarkerMissingTheFieldsThisVersionNeeds(): void
    {
        $this->marker('{"context":"prod-app"}');

        $this->assertNull(CompileMarker::read($this->scriptDir));
    }

    public function testAMarkerWithAnEmptyField(): void
    {
        $this->marker('{"app":"","context":"prod-app"}');

        $this->assertNull(CompileMarker::read($this->scriptDir));
    }

    /** Which build the scripts are, and nothing about where that build writes. */
    public function testMatchesTheApplicationAndContextTheScriptsHold(): void
    {
        CompileMarker::write($this->scriptDir, 'My\App', 'prod-app');

        $this->assertTrue(CompileMarker::matches($this->scriptDir, 'My\App', 'prod-app'));
        $this->assertFalse(CompileMarker::matches($this->scriptDir, 'My\App', 'prod-hal-app'));
        $this->assertFalse(CompileMarker::matches($this->scriptDir, 'Other\App', 'prod-app'));
    }

    private function marker(string $content): void
    {
        file_put_contents(CompileMarker::path($this->scriptDir), $content);
    }
}
