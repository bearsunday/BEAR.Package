<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Router;

use Aura\Cli\CliFactory;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

use function assert;
use function file_exists;
use function file_put_contents;
use function serialize;
use function unlink;
use function unserialize;

class CliRouterTest extends TestCase
{
    private \BEAR\Package\Provide\Router\CliRouter $router;

    private string $stdInFile;

    protected function setUp(): void
    {
        $stdOut = __DIR__ . '/stdout.log';
        $this->stdInFile = __DIR__ . '/stdin.text';
        $stdIo = (new CliFactory())->newStdio('php://stdin', $stdOut);
        $httpMethodParams = new HttpMethodParams();
        $httpMethodParams->setStdIn($this->stdInFile);
        $this->router = new CliRouter(new WebRouter('page://self', $httpMethodParams), $stdIo);
        $this->router->setStdIn($this->stdInFile);
    }

    protected function tearDown(): void
    {
        @unlink(__DIR__ . '/stdin.text');
        @unlink(__DIR__ . '/stdout.log');
    }

    /**
     * @return (string|string[])[][]
     * @phpstan-return array{0:              array{0: string, 1: string, 2: array<string>, 3: array<string>, 4: string}}
     */
    public function argvProvider(): array
    {
        return [
            ['get', 'page://self/?name=bear', ['name' => 'bear'], [], ''],
            ['post', 'page://self/?name=bear', [], ['name' => 'bear'], ''],
            ['put', 'page://self/?name=bear', [], [], 'name=bear'],
            ['patch', 'page://self/?name=bear', [], [], 'name=bear'],
            ['delete', 'page://self/?name=bear', [], [], 'name=bear'],
        ];
    }

    /**
     * @dataProvider argvProvider
     */

    /**
     * @param array<string, string> $get
     * @param array<string, string> $post
     *
     * @dataProvider argvProvider
     */
    public function testMatch(string $argv2, string $argv3, array $get, array $post, string $stdin): void
    {
        $server = [
            'argv' => [
                'php',
                $argv2,
                $argv3,
            ],
            'argc' => 3,
        ];
        $globals = [
            '_GET' => $get,
            '_POST' => $post,
        ];
        if ($stdin) {
            file_put_contents($this->stdInFile, $stdin);
        }

        $request = $this->router->match($globals, $server); // @phpstan-ignore-line
        $this->assertSame($argv2, $request->method);
        $this->assertSame('page://self/', $request->path);
        $this->assertSame(['name' => 'bear'], $request->query);
    }

    public function testOption(): void
    {
        $server = [
            'argv' => [
                'php',
                'options',
                'page://self/',
            ],
            'argc' => 3,
        ];
        $globals = [
            '_GET' => [],
            '_POST' => [],
        ];
        $request = $this->router->match($globals, $server); // @phpstan-ignore-line
        $this->assertSame('options', $request->method);
        $this->assertSame('page://self/', $request->path);
    }

    public function testGenerate(): void
    {
        $actual = $this->router->generate('', []);
        $this->assertFalse((bool) $actual);
    }

    public function testError(): void
    {
        $this->router->setTerminateException(new InvalidArgumentException());
        $this->expectException(InvalidArgumentException::class);
        $server = [
            'argv' => [
                'php',
                'get',
            ],
            'argc' => 2,
        ];
        $globals = [
            '_GET' => [],
            '_POST' => [],
        ];
        $this->router->match($globals, $server); // @phpstan-ignore-line
    }

    public function testStdInCleanup(): void
    {
        file_put_contents($this->stdInFile, '');
        $exists = file_exists($this->stdInFile);
        $this->assertTrue($exists);
        unset($this->router);
        $exists = file_exists($this->stdInFile);
        $this->assertFalse($exists);
    }

    public function testSerializable(): void
    {
        $router = unserialize(serialize($this->router));
        assert($router instanceof CliRouter);
        $router->setTerminateException(new InvalidArgumentException());
        $this->expectException(InvalidArgumentException::class);
        $router->match(
            [
                '_GET' => [],
                '_POST' => [],
            ],
            [ // @phpstan-ignore-line
                'argc' => 1,
                'argv' => ['page.php'],
            ]
        );
    }
}
