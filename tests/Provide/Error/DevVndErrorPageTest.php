<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Error;

use BEAR\Resource\Exception\ResourceNotFoundException;
use BEAR\Sunday\Extension\Router\RouterMatch;
use LogicException;
use PHPUnit\Framework\TestCase;

use function json_decode;

class DevVndErrorPageTest extends TestCase
{
    private DevVndErrorPage $page;

    protected function setUp(): void
    {
        parent::setUp();

        $e = new LogicException('bear');
        $request = new RouterMatch();
        [$request->method, $request->path, $request->query] = ['get', '/', []];
        $this->page = (new DevVndErrorPageFactory())->newInstance($e, $request);
    }

    public function testToString(): void
    {
        $actual = (string) $this->page;

        $expected = '{
        "message": "Internal Server Error",
        "logref": "{logref}",
        "request": "get /",
        "exceptions": "LogicException(bear)"
    }';

        $actualJson = json_decode($actual, true);
        $expectedJson = json_decode($expected, true);

        $this->assertIsArray($actualJson);
        $this->assertIsArray($expectedJson);

        /** @var array{message: string, logref: string, request: string, exceptions: string, file: string} $actualJson */
        /** @var array{message: string, logref: string, request: string, exceptions: string} $expectedJson */

        $this->assertArrayHasKey('message', $actualJson);
        $this->assertArrayHasKey('logref', $actualJson);
        $this->assertArrayHasKey('request', $actualJson);
        $this->assertArrayHasKey('exceptions', $actualJson);
        $this->assertArrayHasKey('file', $actualJson);

        $this->assertSame($expectedJson['message'], $actualJson['message']);
        $this->assertSame($expectedJson['logref'], $actualJson['logref']);
        $this->assertSame($expectedJson['request'], $actualJson['request']);
        $this->assertSame($expectedJson['exceptions'], $actualJson['exceptions']);
    }

    /** ErrorLogger writes a logref for server errors only, so a 4xx page must not name one. */
    public function testClientErrorHasNoLogref(): void
    {
        $request = new RouterMatch();
        [$request->method, $request->path, $request->query] = ['get', '/', []];
        $page = (new DevVndErrorPageFactory())->newInstance(new ResourceNotFoundException('/__not_found__'), $request);

        $body = json_decode((string) $page, true);

        $this->assertIsArray($body);
        $this->assertArrayNotHasKey('logref', $body);
        $this->assertArrayHasKey('exceptions', $body);
    }
}
