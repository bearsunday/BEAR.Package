<?php

declare(strict_types=1);

namespace BEAR\Package;

use BEAR\Sunday\Extension\Application\AppInterface;
use FakeVendor\HelloWorld\Module\App;
use PHPUnit\Framework\TestCase;
use Ray\Di\AbstractModule;
use Ray\Di\Injector as RayInjector;
use Ray\Di\InjectorInterface;

use function array_fill;
use function assert;
use function file_get_contents;
use function file_put_contents;
use function microtime;
use function passthru;
use function spl_object_hash;
use function sprintf;
use function touch;

use const E_ALL;

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

    /** @depends testRayInjector */
    public function testRayInjectorAsSingleton(RayInjector $injector): void
    {
        $singletonInjector = Injector::getInstance('FakeVendor\HelloWorld', 'app', __DIR__ . '/Fake/fake-app');
        $this->assertSame(spl_object_hash($injector), spl_object_hash($singletonInjector));
    }

    /** @return array<array{0: string, 1:int}> */
    public function countOfNewProvider(): array
    {
        return [
            ['prod-app', 0],
            //            ['app', 1]
        ];
    }

    /** @dataProvider countOfNewProvider */
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

    /** @dataProvider countOfNewProvider */
    public function testRaceConditionBoot(string $context): void
    {
        $cn = microtime();
        $cmd = sprintf('php -d error_reporting=%s %s/script/boot.php -c%s -n%s', (string) E_ALL, __DIR__, $context, $cn);
        $errorLog = __DIR__ . '/script/error.log';
        file_put_contents($errorLog, '');
        $cmds = array_fill(0, 7, $cmd);
        $exitCode = (new AsyncRun())($cmds, $errorLog);
        // no error should be recorded
        $this->assertSame('', file_get_contents($errorLog));
        $this->assertSame(0, $exitCode);
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
