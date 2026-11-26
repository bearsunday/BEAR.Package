<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use BEAR\AppMeta\Meta;
use BEAR\Package\Injector;
use PHPUnit\Framework\TestCase;

use function dirname;

class BootstrapTest extends TestCase
{
    private const string APP_NAME = 'FakeVendor\HelloWorld';

    /** The compile records what loaded up to the failure and goes on. */
    public function testAFailedRequestLeavesNoResponse(): void
    {
        $appDir = dirname(__DIR__) . '/Fake/fake-app';
        $injector = Injector::getInstance(self::APP_NAME, 'cli-app', $appDir);
        $server = ['REQUEST_URI' => '/no-such-page', 'REQUEST_METHOD' => 'GET', 'argc' => 3, 'argv' => ['', 'get', 'page://self/no-such-page']];

        $response = (new Bootstrap(new Meta(self::APP_NAME, 'cli-app', $appDir), $injector))(self::APP_NAME, 'cli-app', ['_GET' => [], '_POST' => []], $server);

        $this->assertNull($response);
    }
}
