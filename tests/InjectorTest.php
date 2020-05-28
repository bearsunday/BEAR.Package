<?php

declare(strict_types=1);

namespace BEAR\Package;

use BEAR\Sunday\Extension\Application\AppInterface;
use FakeVendor\HelloWorld\Module\App;
use PHPUnit\Framework\TestCase;

class InjectorTest extends TestCase
{
    protected function setUp() : void
    {
        parent::setUp();
    }

    /**
     * @return array<array{0: string, 1:int}>
     */
    public function CountOfNewProvider() : array
    {
        return [
            ['prod-app', 0],
            //            ['app', 1]
        ];
    }

    /**
     * @dataProvider CountOfNewProvider
     */
    public function testCachedGetInstance(string $context, int $countOfNew) : void
    {
        $appDir = __DIR__ . '/Fake/fake-app';
        $cn = (string) getmypid();
        $exitCode = $this->runOnce($context, $cn);
        $this->assertSame(0, $exitCode);
        App::$counfOfNew = 0;
        $injector = Injector::getInstance('FakeVendor\HelloWorld', $context, $appDir, $cn);
        /** @var App $app */
        $app = $injector->getInstance(AppInterface::class);
        $this->assertInstanceOf(App::class, $app);
        $this->assertSame($countOfNew, App::$counfOfNew);
        // 2nd injector; AppInterface object should be stored as a singleton.
        $injector = Injector::getInstance('FakeVendor\HelloWorld', $context, $appDir, $cn);
        /** @var App $app */
        $app = $injector->getInstance(AppInterface::class);
        $this->assertInstanceOf(App::class, $app);
        $this->assertSame($countOfNew, App::$counfOfNew);
    }

    /**
     * @dataProvider CountOfNewProvider
     */
    public function testRaceConditionBoot(string $context) : void
    {
        $cn = microtime();
        $cmd = sprintf('php -d error_reporting=%s %s/script/boot.php -c%s -n%s', (string) E_ALL, __DIR__, $context, $cn);
        $errorLog = __DIR__ . '/script/error.log';
        file_put_contents($errorLog, '');
        $cmds = array_fill(0, 7, $cmd);
        $exitCode = (new AsyncRun)($cmds, $errorLog);
        // no error should be recorded
        $this->assertSame('', file_get_contents($errorLog));
        $this->assertSame(0, $exitCode);
    }

    private function runOnce(string $context, string $cn) : int
    {
        $cmd = sprintf('php %s/script/boot.php -c%s -n%s', __DIR__, $context, $cn);
        passthru($cmd, $exitCode);

        return $exitCode;
    }
}
