<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use BEAR\AppMeta\Meta;
use BEAR\Package\Injector;
use PHPUnit\Framework\TestCase;

use function dirname;
use function file_get_contents;
use function sys_get_temp_dir;
use function touch;
use function uniqid;
use function unlink;

class BootstrapTest extends TestCase
{
    private const string APP_NAME = 'FakeVendor\HelloWorld';
    private string $failureLog;

    protected function setUp(): void
    {
        $this->failureLog = sys_get_temp_dir() . '/bear-package-failure-' . uniqid('', true);
        touch($this->failureLog);
    }

    protected function tearDown(): void
    {
        unlink($this->failureLog);
    }

    /** A failed request leaves preload.php without what the rest of it loads, so the recording says so. */
    public function testAFailedRequestIsReported(): void
    {
        $this->assertNull($this->request('/fails'));
        $this->assertSame("get page://self/fails failed while recording preload.php: RuntimeException: store unreachable\n", file_get_contents($this->failureLog));
    }

    /** An application with nothing at the path, such as an API-only one at "/", has nothing to report. */
    public function testAMissingResourceIsNotReported(): void
    {
        $this->assertNull($this->request('/no-such-page'));
        $this->assertSame('', file_get_contents($this->failureLog));
    }

    private function request(string $path): mixed
    {
        $appDir = dirname(__DIR__) . '/Fake/fake-app';
        $injector = Injector::getInstance(self::APP_NAME, 'cli-app', $appDir);
        $server = ['REQUEST_URI' => $path, 'REQUEST_METHOD' => 'GET', 'argc' => 3, 'argv' => ['', 'get', 'page://self' . $path]];

        return (new Bootstrap(new Meta(self::APP_NAME, 'cli-app', $appDir), $injector, $this->failureLog))(self::APP_NAME, 'cli-app', ['_GET' => [], '_POST' => []], $server);
    }
}
