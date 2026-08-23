<?php

declare(strict_types=1);

namespace BEAR\Package\Module;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\AppMeta\Meta;
use BEAR\Package\Context\ProdModule;
use BEAR\Package\Module;
use PHPUnit\Framework\TestCase;
use Ray\Di\AbstractModule;
use Ray\Di\Injector;

use function dirname;
use function mkdir;
use function sys_get_temp_dir;
use function uniqid;

class ReadOnlyAppModuleTest extends TestCase
{
    private const APP_NAME = 'FakeVendor\HelloWorld';

    private string $declared;
    private Meta $edge;

    protected function setUp(): void
    {
        parent::setUp();

        $this->declared = sys_get_temp_dir() . '/bear-read-only-' . uniqid('', true);
        mkdir($this->declared . '/tmp', 0777, true);
        mkdir($this->declared . '/log', 0777, true);
        $this->edge = new Meta(self::APP_NAME, 'prod-app', dirname(__DIR__) . '/Fake/fake-app');
    }

    /**
     * The application's own module chain decides where it writes, not the caller of the boot,
     * and the path it named is the path used - no resolving, so a deployment reads back what
     * it wrote.
     */
    public function testDeclaredDirectoriesReachTheContainer(): void
    {
        $meta = $this->resolve(new ReadOnlyAppModule($this->declared . '/tmp', $this->declared . '/log'));

        $this->assertSame($this->declared . '/tmp', $meta->tmpDir);
        $this->assertSame($this->declared . '/log', $meta->logDir);
    }

    /** Only tmp and log are the declaration's to give: the tree and the application are the compile's. */
    public function testDeclarationLeavesTheRestOfTheMetaAlone(): void
    {
        $meta = $this->resolve(new ReadOnlyAppModule($this->declared . '/tmp', $this->declared . '/log'));

        $this->assertSame($this->edge->name, $meta->name);
        $this->assertSame($this->edge->appDir, $meta->appDir);
        $this->assertSame($this->edge->buildDir, $meta->buildDir);
    }

    /** Nothing installed: the Meta the boot resolved is the one the container gets. */
    public function testWithoutTheModuleTheBootsMetaIsBound(): void
    {
        $meta = $this->resolve(null);

        $this->assertSame($this->edge->tmpDir, $meta->tmpDir);
        $this->assertSame($this->edge->logDir, $meta->logDir);
    }

    /**
     * AppMetaModule is wrapped around the context chain, so a declaration inside that chain has
     * to survive being wrapped - which is the ordering this whole module depends on.
     */
    private function resolve(ReadOnlyAppModule|null $readOnly): AbstractAppMeta
    {
        $app = new class ($readOnly) extends AbstractModule {
            public function __construct(private ReadOnlyAppModule|null $readOnly)
            {
                parent::__construct();
            }

            protected function configure(): void
            {
                if ($this->readOnly === null) {
                    return;
                }

                $this->install($this->readOnly);
            }
        };

        $module = new AppMetaModule($this->edge, new ProdModule($app));
        $meta = (new Injector($module))->getInstance(AbstractAppMeta::class);
        $this->assertInstanceOf(AbstractAppMeta::class, $meta);

        return $meta;
    }

    /** The real chain builder wraps AppMetaModule the same way, or the module above is a fiction. */
    public function testModuleBuilderWrapsAppMetaModuleRoundTheChain(): void
    {
        $module = (new Module())($this->edge, 'prod-app');

        $this->assertInstanceOf(AppMetaModule::class, $module);
    }
}
