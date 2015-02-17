<?php

namespace BEAR\Package\Provide\Error;

use BEAR\AppMeta\AppMeta;
use BEAR\Package\Provide\Transfer\FakeHttpResponder;
use BEAR\Resource\Exception\BadRequestException;
use BEAR\Resource\Exception\ResourceNotFoundException;
use BEAR\Resource\Exception\ServerErrorException;
use BEAR\Sunday\Extension\Router\RouterMatch;

class VndErrorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var VndError
     */
    private $vndError;

    public function setUp()
    {
        FakeHttpResponder::reset();
        $this->vndError = new VndError(new AppMeta('FakeVendor\HelloWorld'), new FakeHttpResponder());
    }

    public function testNotFound()
    {
        $e = new ResourceNotFoundException('', 404);
        $this->vndError->handle($e, new RouterMatch)->transfer();
        $this->assertSame(404, FakeHttpResponder::$code);
        $this->assertSame(['Content-Type' => 'application/vnd.error+json'], FakeHttpResponder::$headers);
    }

    public function testBadRequest()
    {
        $e = new BadRequestException('invalid-method', 400);
        $this->vndError->handle($e, new RouterMatch)->transfer();
        $this->assertSame(400, FakeHttpResponder::$code);
    }

    public function testServerError()
    {
        $e = new ServerErrorException('message', 501);
        $this->vndError->handle($e, new RouterMatch)->transfer();
        $this->assertSame(501, FakeHttpResponder::$code);
    }

    public function testServerErrorNot50X()
    {
        $e = new \RuntimeException('message', 0);
        $this->vndError->handle($e, new RouterMatch)->transfer();
        $this->assertSame(500, FakeHttpResponder::$code);
    }
}
