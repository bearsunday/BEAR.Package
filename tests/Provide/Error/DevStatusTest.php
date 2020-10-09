<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Error;

use BEAR\Resource\Exception\BadRequestException;
use BEAR\Resource\Exception\ResourceNotFoundException;
use BEAR\Sunday\Extension\Router\RouterMatch;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class DevStatusTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $request = new RouterMatch();
        [$request->method, $request->path, $request->query] = ['get', '/', []];
    }

    public function testRuntimeException(): void
    {
        $e = new RuntimeException();
        $status = new Status($e);
        $this->assertSame(503, $status->code);
    }

    public function testBadRequest(): void
    {
        $e = new BadRequestException();
        $status = new Status($e);
        $this->assertSame(400, $status->code);
    }

    public function testNotFound(): void
    {
        $e = new ResourceNotFoundException('/__not_found__');
        $status = new Status($e);
        $this->assertSame(404, $status->code);
    }
}
