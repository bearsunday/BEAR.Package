<?php

declare(strict_types=1);

namespace BEAR\Package;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\Sunday\Extension\Application\AppInterface;
use FakeVendor\HelloWorld\Module\App;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;
use Ray\Di\AbstractModule;
use Ray\Di\Injector as RayInjector;
use Ray\Di\InjectorInterface;

use function assert;
use function mkdir;
use function passthru;
use function realpath;
use function spl_object_hash;
use function sprintf;
use function sys_get_temp_dir;
use function touch;
use function uniqid;

class InjectorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testRayInjector(): InjectorInterface
    {
        $injector = Injector::getInstance('FakeVendor\HelloWorld', 'app', __DIR__ . '/Fake/fake-app');
        $this->assertInstanceOf(RayInjector::class, $injector);

        return $injector;
    }

    #[Depends('testRayInjector')]
    public function testRayInjectorAsSingleton(RayInjector $injector): void
    {
        $singletonInjector = Injector::getInstance('FakeVendor\HelloWorld', 'app', __DIR__ . '/Fake/fake-app');
        $this->assertSame(spl_object_hash($injector), spl_object_hash($singletonInjector));
    }

    /** @return array<array{0: non-empty-string, 1:int}> */
    public static function countOfNewProvider(): array
    {
        return [
            ['prod-app', 0],
            //            ['app', 1]
        ];
    }

    /** @param non-empty-string $context */
    #[DataProvider('countOfNewProvider')]
    public function testCachedGetInstance(string $context, int $countOfNew): void
    {
        $appDir = __DIR__ . '/Fake/fake-app';
        $exitCode = $this->runOnce($context);
        $this->assertSame(0, $exitCode);
        App::$countOfNewInstance = 0;
        $injector = Injector::getInstance('FakeVendor\HelloWorld', $context, $appDir);
        $app = $injector->getInstance(AppInterface::class);
        assert($app instanceof AppInterface);
        $this->assertInstanceOf(AppInterface::class, $app);
        $count = App::$countOfNewInstance;
        // 2nd injector; AppInterface object should be stored as a singleton.
        $injector = Injector::getInstance('FakeVendor\HelloWorld', $context, $appDir);
        $app = $injector->getInstance(AppInterface::class);
        assert($app instanceof AppInterface);
        $this->assertInstanceOf(AppInterface::class, $app);
        $this->assertSame($count, App::$countOfNewInstance);
    }

    public function testBindingsModified(): void
    {
        $appDir = __DIR__ . '/Fake/fake-app';
        $context = 'app';
        $exitCode = $this->runOnce($context);
        $this->assertSame(0, $exitCode);
        touch(__DIR__ . '/Fake/fake-app/src/Module/AppModule.php');
        $injector = Injector::getInstance('FakeVendor\HelloWorld', $context, $appDir);
        $this->assertInstanceOf(RayInjector::class, $injector);
    }

    /** Two trees of one application are two deployments, and the second must not be handed the first one's. */
    public function testAppDirIsPartOfInjectorIdentity(): void
    {
        $appDir = __DIR__ . '/Fake/fake-app';
        $otherTree = sys_get_temp_dir() . '/bear-tree-' . uniqid();
        mkdir($otherTree . '/src/Resource', 0777, true);
        $first = Injector::getInstance('FakeVendor\HelloWorld', 'app', $appDir);
        $second = Injector::getInstance('FakeVendor\HelloWorld', 'app', $otherTree);
        $this->assertSame(realpath($appDir), $first->getInstance(AbstractAppMeta::class)->appDir);
        $this->assertSame(realpath($otherTree), $second->getInstance(AbstractAppMeta::class)->appDir);
        deleteFiles($otherTree);
    }

    public function testGetOverrideInstance(): void
    {
        $fakeApp = new class implements AppInterface {
        };
        $injector = $this->getInjector($fakeApp);
        $app = $injector->getInstance(AppInterface::class);
        $this->assertSame($fakeApp, $app);
        // cached instance
        $injector = $this->getInjector($fakeApp);
        $app = $injector->getInstance(AppInterface::class);
        $this->assertSame($fakeApp, $app);
    }

    /** An override injector writes where the default one does: it is the same application. */
    public function testOverrideInstanceWritesWhereTheDefaultOneDoes(): void
    {
        $override = $this->getInjector(new class implements AppInterface {
        });
        $default = Injector::getInstance('FakeVendor\HelloWorld', 'app', __DIR__ . '/Fake/fake-app');

        $this->assertSame(
            $default->getInstance(AbstractAppMeta::class)->tmpDir,
            $override->getInstance(AbstractAppMeta::class)->tmpDir,
        );
    }

    private function getInjector(AppInterface $fakeApp): InjectorInterface
    {
        return Injector::getOverrideInstance(
            'FakeVendor\HelloWorld',
            'app',
            __DIR__ . '/Fake/fake-app',
            new class ($fakeApp) extends AbstractModule {
                public function __construct(private AppInterface $app, AbstractModule|null $module = null)
                {
                    parent::__construct($module);
                }

                protected function configure(): void
                {
                    $this->bind(AppInterface::class)->toInstance($this->app);
                }
            },
        );
    }

    private function runOnce(string $context): int
    {
        $cmd = sprintf('php %s/script/boot.php -c%s', __DIR__, $context);
        passthru($cmd, $exitCode);

        return $exitCode;
    }
}
