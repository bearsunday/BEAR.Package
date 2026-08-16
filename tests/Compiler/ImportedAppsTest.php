<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\Package\Exception\PharImportsUnreadableException;
use BEAR\Package\Injector\AppDirs;
use BEAR\Package\Module\Import\ImportApp;
use BEAR\Package\Module\ImportAppModule;
use BEAR\Resource\Annotation\ImportAppConfig;
use BEAR\Resource\Module\ResourceModule;
use PHPUnit\Framework\TestCase;
use Ray\Compiler\Compiler as RayCompiler;
use Ray\Di\AbstractModule;

use function BEAR\Package\deleteFiles;
use function dirname;
use function mkdir;
use function sys_get_temp_dir;
use function uniqid;

class ImportedAppsTest extends TestCase
{
    /** @var non-empty-string */
    private string $scriptDir;

    protected function setUp(): void
    {
        $this->scriptDir = sys_get_temp_dir() . '/bear-imported-' . uniqid();
        mkdir($this->scriptDir, 0777, true);
    }

    protected function tearDown(): void
    {
        deleteFiles($this->scriptDir);
    }

    /** What ImportAppModule wrote at build time is what the packer reads back. */
    public function testTheContainerDeclaresWhatItImports(): void
    {
        $module = new ResourceModule('FakeVendor\HelloWorld');
        $module->override(new ImportAppModule([new ImportApp('foo', 'Import\HelloWorld', 'app')]));
        // The Meta alone: a compile resolves every binding, and AppInterface is not what is under test.
        $module->override($this->meta());
        $this->compile($module);

        $imports = ImportedApps::of($this->scriptDir);

        $this->assertCount(1, $imports);
        $this->assertSame('Import\HelloWorld', $imports[0]->appName);
        $this->assertSame('app', $imports[0]->context);
    }

    /** No binding is the one failure that means "imports nothing". */
    public function testAnApplicationThatImportsNothing(): void
    {
        $this->compile(new ResourceModule('FakeVendor\HelloWorld'));

        $this->assertSame([], ImportedApps::of($this->scriptDir));
    }

    public function testADeclarationThatIsNotAList(): void
    {
        $this->compile($this->declaring('not-a-list'));

        $this->expectException(PharImportsUnreadableException::class);
        ImportedApps::of($this->scriptDir);
    }

    /** A container from a version whose declaration holds something else cannot be packed. */
    public function testADeclarationOfSomethingOtherThanImports(): void
    {
        $this->compile($this->declaring(['Import\HelloWorld']));

        $this->expectException(PharImportsUnreadableException::class);
        ImportedApps::of($this->scriptDir);
    }

    private function meta(): AbstractModule
    {
        $meta = AppDirs::meta('FakeVendor\HelloWorld', 'app', dirname(__DIR__) . '/Fake/fake-app');

        return new class ($meta) extends AbstractModule {
            public function __construct(private AbstractAppMeta $meta)
            {
                parent::__construct();
            }

            protected function configure(): void
            {
                $this->bind(AbstractAppMeta::class)->toInstance($this->meta);
            }
        };
    }

    private function declaring(mixed $config): AbstractModule
    {
        return new class ($config) extends AbstractModule {
            public function __construct(private mixed $config)
            {
                parent::__construct();
            }

            protected function configure(): void
            {
                $this->bind()->annotatedWith(ImportAppConfig::class)->toInstance($this->config);
            }
        };
    }

    private function compile(AbstractModule $module): void
    {
        (new RayCompiler())->compile($module, $this->scriptDir);
    }
}
