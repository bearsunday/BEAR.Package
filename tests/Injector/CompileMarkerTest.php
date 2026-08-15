<?php

declare(strict_types=1);

namespace BEAR\Package\Injector;

use PHPUnit\Framework\TestCase;

use function BEAR\Package\deleteFiles;
use function file_put_contents;
use function mkdir;
use function sys_get_temp_dir;
use function uniqid;

/**
 * A marker this version cannot read is a marker that is not there: the boot recompiles
 * instead of trusting scripts it cannot identify.
 */
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

    /** Only the writable directory decides whether the scripts are the ones this boot needs. */
    public function testMatchesTheWritableDirectoryTheScriptsHold(): void
    {
        CompileMarker::write($this->scriptDir, 'My\App', 'prod-app', '/write/My/App/prod-app/tmp');

        $this->assertTrue(CompileMarker::matches($this->scriptDir, '/write/My/App/prod-app/tmp'));
        $this->assertFalse(CompileMarker::matches($this->scriptDir, '/other/My/App/prod-app/tmp'));
    }

    private function marker(string $content): void
    {
        file_put_contents(CompileMarker::path($this->scriptDir), $content);
    }
}
