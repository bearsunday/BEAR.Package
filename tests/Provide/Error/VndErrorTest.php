<?php

namespace BEAR\Package\Provide\Error;

use BEAR\Package\AppMeta;
use BEAR\Resource\Exception\BadRequestException;
use BEAR\Resource\Exception\ResourceNotFoundException;
use BEAR\Resource\Exception\ServerErrorException;
use BEAR\Sunday\Extension\Router\RouterMatch;

require_once dirname(dirname(__DIR__)) . '/Fake/Provide/Error/header.php';
require_once dirname(dirname(__DIR__)) . '/Fake/Provide/Error/http_response_code.php';

class VndErrorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FakeVndError
     */
    private $vndError;

    public function setUp()
    {
        FakeVndError::reset();
        $this->vndError = new FakeVndError(new AppMeta('FakeVendor\HelloWorld'));
    }

    public function testNotFound()
    {
        $e = new ResourceNotFoundException('', 404);
        $this->vndError->handle($e, new RouterMatch)->transfer();
        $this->assertSame([404], FakeVndError::$code);
        $this->assertSame(['Content-Type: application/vnd.error+json'], FakeVndError::$headers);
    }

    public function testBadRequest()
    {
        $e = new BadRequestException('invalid-method', 400);
        $this->vndError->handle($e, new RouterMatch)->transfer();
        $this->assertSame([400], FakeVndError::$code);
    }

    public function testServerError()
    {
        $e = new ServerErrorException('message', 501);
        $this->vndError->handle($e, new RouterMatch)->transfer();
        $this->assertSame([501], FakeVndError::$code);
    }

    public function testServerErrorNot50X()
    {
        $e = new \RuntimeException('message', 0);
        $this->vndError->handle($e, new RouterMatch)->transfer();
        $this->assertSame([500], FakeVndError::$code);
    }
}
